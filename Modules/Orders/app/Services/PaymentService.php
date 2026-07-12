<?php

declare(strict_types=1);

namespace Modules\Orders\Services;

use Illuminate\Support\Facades\DB;
use Modules\Orders\Contracts\PaymentGateway;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Enums\PaymentStatus;
use Modules\Orders\Events\PaymentVerified;
use Modules\Orders\Exceptions\PaymentNotPayableException;
use Modules\Orders\Exceptions\PaymentVerificationFailedException;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;

final readonly class PaymentService
{
    public function __construct(
        private PaymentGateway $gateway,
        private OrderTransitionService $transition,
        private HoldExpiryService $holdExpiry,
    ) {}

    public function initiate(Order $order, string $callbackUrl): Payment
    {
        // hold مرده را همین‌جا دفن کن — نه در صفحهٔ بانک
        if ($this->holdExpiry->expire($order)) {
            throw PaymentNotPayableException::holdExpired($order->public_id);
        }

        if ($order->status === OrderStatus::Pending) {
            $this->transition->transition($order, OrderStatus::AwaitingPayment);
        } elseif ($order->status !== OrderStatus::AwaitingPayment) {
            throw PaymentNotPayableException::wrongStatus($order->public_id, $order->status->name);
        }

        $payment = new Payment([
            'order_id' => $order->id,
            'gateway' => $this->gateway->name(),
            'amount' => $order->total_amount,
        ]);
        $payment->save();

        $init = $this->gateway->initiate(
            amount: $order->total_amount,
            callbackUrl: $callbackUrl,
            reference: $payment->public_id,
        );

        $payment->forceFill([
            'status' => PaymentStatus::Redirected,
            'gateway_token' => $init->gatewayToken,
            'gateway_meta' => ['redirect_url' => $init->redirectUrl],
        ])->save();

        return $payment->refresh();
    }

    /** callback: راستی‌آزمایی server-side و بستن چرخه. */
    public function handleCallback(string $gatewayToken): Payment
    {
        /** @var Payment $payment */
        $payment = Payment::query()
            ->where('gateway_token', $gatewayToken)
            ->firstOrFail();

        if ($payment->status->isFinal()) {
            return $payment;   // idempotency — callback تکراری
        }

        $result = $this->gateway->verify($gatewayToken, $payment->amount);

        if (! $result->success) {
            // ثبت شکست باید بماند — پس بیرون از transaction و قبل از throw
            $payment->forceFill([
                'status' => PaymentStatus::Failed,
                'gateway_meta' => $result->raw,
            ])->save();

            throw PaymentVerificationFailedException::because($result->failureReason ?? 'unknown');
        }

        return DB::transaction(function () use ($payment, $result): Payment {
            // قفل + چک دوباره: دو callback موفقِ همزمان — دومی اینجا می‌ایستد
            $fresh = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($fresh->status->isFinal()) {
                return $fresh;
            }

            $fresh->forceFill([
                'status' => PaymentStatus::Verified,
                'gateway_ref' => $result->referenceId,
                'gateway_meta' => $result->raw,
                'verified_at' => now(),
            ])->save();

            $this->transition->transition($fresh->order, OrderStatus::Paid, reason: 'gateway_verified');

            event(new PaymentVerified(
                paymentPublicId: $fresh->public_id,
                orderPublicId: $fresh->order->public_id,
                amount: $fresh->amount->amount,
                gatewayRef: (string) $result->referenceId,
            ));

            return $fresh->refresh();
        });
    }



}
