<?php

namespace App\Actions\MQTT;

use App\Models\User;
use Salman\Mqtt\Facades\Mqtt;

class MQTTPublishAction
{
    public function execute(string $topic, string $message): bool
    {
        $clientId = User::getAuthenticated()->id;

        return Mqtt::ConnectAndPublish($topic, $message, $clientId);
    }
}
