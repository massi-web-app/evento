<?php


declare(strict_types=1);

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Events\Models\TicketType;
use Modules\Orders\Exceptions\PaymentVerificationFailedException;
use Modules\Orders\Http\Requests\HoldOrderRequest;
use Modules\Orders\Http\Requests\InitiatePaymentRequest;
use Modules\Orders\Http\Resources\OrderResource;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\HoldService;
use Modules\Orders\Services\PaymentService;

final  class CheckoutController extends Controller
{
    use AuthorizesRequests;
    public function hold(HoldOrderRequest $request, HoldService $holdService): JsonResponse
    {
        /** @var TicketType $ticketType */
        $ticketType = TicketType::query()
            ->where('public_id', $request->ticketTypePublicId())
            ->with(['prices', 'session'])
            ->firstOrFail();

        $order = $holdService->hold(
            userId: (int) $request->user()->getAuthIdentifier(),
            ticketType: $ticketType,
            quantity: $request->quantity(),
        );

        return OrderResource::make($order->load('items'))
            ->response()
            ->setStatusCode(201);
    }

    public function pay(InitiatePaymentRequest $request, Order $order, PaymentService $paymentService): JsonResponse
    {
        $this->authorize('pay', $order);

        $payment = $paymentService->initiate(
            order: $order,
            callbackUrl: route('api.payments.callback'),
        );

        return response()->json([
            'payment_id' => $payment->public_id,
            'redirect_url' => $payment->gateway_meta['redirect_url'],
        ]);
    }

    public function callback(Request $request, PaymentService $paymentService): RedirectResponse
    {
        $token = (string) $request->query('token', '');
        $frontendBase = (string) config('orders.frontend_result_url');

        if ($token === '') {
            return redirect()->away($frontendBase . '?status=invalid');
        }

        try {
            $payment = $paymentService->handleCallback($token);
        } catch (PaymentVerificationFailedException) {
            return redirect()->away($frontendBase . '?status=failed');
        }

        return redirect()->away(
            $frontendBase . '?status=success&order=' . $payment->order->public_id,
        );
    }


}
