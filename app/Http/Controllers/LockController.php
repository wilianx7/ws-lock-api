<?php

namespace App\Http\Controllers;

use App\Actions\Lock\CreateOrUpdateLockAction;
use App\DTOs\LockDTO;
use App\Http\Requests\CreateOrUpdateLockRequest;
use App\Http\Resources\GenericResource;
use App\Http\Resources\GenericResourceCollection;
use App\Models\Lock;
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
                AllowedFilter::exact('mac_address'),
                AllowedFilter::scope('term', 'whereTerm'),
            ])
            ->allowedIncludes([
                'users',
            ])
            ->with($request->input('with') ?? [])
            ->getQuery();

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
        Lock::destroy($this->getIds('lock'));

        return response(null, 204);
    }
}
