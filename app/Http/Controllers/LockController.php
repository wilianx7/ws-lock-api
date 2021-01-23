<?php

namespace App\Http\Controllers;

use App\Actions\Lock\CreateOrUpdateLockAction;
use App\DTOs\LockDTO;
use App\Http\Requests\CreateOrUpdateLockRequest;
use App\Http\Resources\GenericResource;
use App\Http\Resources\GenericResourceCollection;
use App\Models\Lock;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LockController extends Controller
{
    private CreateOrUpdateLockAction $createOrUpdateLockAction;

    public function __construct(CreateOrUpdateLockAction $createOrUpdateLockAction)
    {
        $this->createOrUpdateLockAction = $createOrUpdateLockAction;
    }

    public function index(Request $request)
    {
        $users = QueryBuilder::for(Lock::class)
            ->allowedSorts([
                'name',
            ])
            ->allowedFilters([
                'name',
                AllowedFilter::exact('id'),
                AllowedFilter::exact('created_by_user_id'),
                AllowedFilter::exact('mac_address'),
                AllowedFilter::scope('term', 'whereTerm'),
            ])
            ->allowedIncludes([
                'users',
                'createdByUser',
            ])
            ->with($request->input('with') ?? []);

        return new GenericResourceCollection($users);
    }

    public function createOrUpdate(CreateOrUpdateLockRequest $request)
    {
        $lockDTO = LockDTO::fromCollection(collect($request->input('lock_data')));

        $lock = $this->createOrUpdateLockAction->execute($lockDTO);

        return new GenericResource($lock);
    }

    public function show($id)
    {
        return new GenericResource($this->find(new Lock(), $id));
    }

    public function destroy()
    {
        foreach ($this->getIds('lock') as $id) {
            $lockToDestroy = Lock::findOrFail($id);

            if ($lockToDestroy->created_by_user_id != User::getAuthenticated()->id) {
                abort(401, 'Unauthorized action!');
            }

            $lockToDestroy->delete();

            DB::table('user_has_locks')
                ->where('lock_id', $id)
                ->delete();
        }

        return response(null, 204);
    }
}
