<?php

namespace App\Http\Controllers;

use App\Actions\MQTT\MQTTPublishAction;
use App\Http\Requests\MQTTRequest;
use App\Models\Lock;

class MQTTController extends Controller
{
    private MQTTPublishAction $MQTTPublishAction;

    public function __construct(MQTTPublishAction $MQTTPublishAction)
    {
        $this->MQTTPublishAction = $MQTTPublishAction;
    }

    public function closeDoor(MQTTRequest $request)
    {
        $macAddress = $request->input('mac_address');

        if (!Lock::where('mac_address', $macAddress)->exists()) {
            abort(401, 'Non-existent lock!');
        }

        $result = $this->MQTTPublishAction->execute('CLOSE_DOOR', $macAddress);

        return response($result ? 'success' : 'failed', 200);
    }

    public function openDoor(MQTTRequest $request)
    {
        $macAddress = $request->input('mac_address');

        if (!Lock::where('mac_address', $macAddress)->exists()) {
            abort(401, 'Non-existent lock!');
        }

        $result = $this->MQTTPublishAction->execute('OPEN_DOOR', $macAddress);

        return response($result ? 'success' : 'failed', 200);
    }
}
