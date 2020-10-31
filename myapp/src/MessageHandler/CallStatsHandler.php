<?php
namespace App\MessageHandler;

use App\Message\CallStats;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CallStatsHandler implements MessageHandlerInterface
{
    public function __invoke(CallStats $message)
    {
        // ... do some work
        $message->getStats();
    }
}
