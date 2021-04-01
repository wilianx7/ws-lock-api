<?php

namespace App\Http\Controllers;

use App\Actions\LockHistory\CreateLockHistoryAction;
use App\Actions\MQTT\MQTTPublishAction;
use App\Enums\LockStateEnum;
use App\Http\Requests\MQTTRequest;
use App\Models\Lock;

class MQTTController extends Controller
{
    private MQTTPublishAction $MQTTPublishAction;
    private CreateLockHistoryAction $createLockHistoryAction;

    public function __construct(
        MQTTPublishAction $MQTTPublishAction,
        CreateLockHistoryAction $createLockHistoryAction
    ) {
        $this->MQTTPublishAction = $MQTTPublishAction;
        $this->createLockHistoryAction = $createLockHistoryAction;
    }

    public function closeDoor(MQTTRequest $request)
    {
        $macAddress = $request->input('mac_address');

        $lock = Lock::where('mac_address', $macAddress)->first();

        if (!$lock) {
            abort(401, 'Non-existent lock!');
        }

        $result = $this->MQTTPublishAction->execute('CLOSE_DOOR', $macAddress);

        if ($result) {
            $lock->update([
                'state' => LockStateEnum::LOCKED()
            ]);

            $this->createLockHistoryAction->execute($lock, 'Fechadura trancada!');
        }

        return response()->json($result ? 'success' : 'failed');
    }

    public function openDoor(MQTTRequest $request)
    {
        $macAddress = $request->input('mac_address');

        $lock = Lock::where('mac_address', $macAddress)->first();

        if (!$lock) {
            abort(401, 'Non-existent lock!');
        }

        $result = $this->MQTTPublishAction->execute('OPEN_DOOR', $macAddress);

        if ($result) {
            $lock->update([
                'state' => LockStateEnum::OPENED()
            ]);

            $this->createLockHistoryAction->execute($lock, 'Fechadura aberta!');
        }

        return response()->json($result ? 'success' : 'failed');
    }
}
