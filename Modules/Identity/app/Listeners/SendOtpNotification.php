<?php
declare(strict_types=1);

namespace Modules\Identity\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Identity\Events\OtpRequested;

final class SendOtpNotification implements ShouldQueue
{

    public string $queue = 'notifications';


    public function handle(OtpRequested $event): void
    {

        // TODO(M16): تحویل به NotificationService — فعلاً لاگ توسعه

        Log::info('OTP issued',[
            'identifier' => $event->identifier,
            'channel' => $event->channel->name,
            'ttl' => $event->ttlSeconds,
        ]);

    }


}
