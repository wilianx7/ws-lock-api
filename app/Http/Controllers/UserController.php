<?php

namespace App\Http\Controllers;

use App\Actions\User\CreateOrUpdateUserAction;
use App\DTOs\UserDTO;
use App\Http\Requests\CreateOrUpdateUserRequest;
use App\Http\Resources\GenericResource;
use App\Http\Resources\GenericResourceCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    private CreateOrUpdateUserAction $createOrUpdateUserAction;

    public function __construct(CreateOrUpdateUserAction $createOrUpdateUserAction)
    {
        $this->createOrUpdateUserAction = $createOrUpdateUserAction;
    }

    public function index(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->allowedSorts([
                'id',
                'name',
                'email',
                'login',
            ])
            ->allowedFilters([
                'name',
                'email',
                'login',
                AllowedFilter::exact('id'),
                AllowedFilter::scope('term', 'whereTerm'),
            ])
            ->allowedIncludes([
                'locks',
            ])
            ->with($request->input('with') ?? [])
            ->getQuery();

        return new GenericResourceCollection($users);
    }

    public function createOrUpdate(CreateOrUpdateUserRequest $request)
    {
        $userDTO = UserDTO::fromCollection(collect($request->input('user_data')));

        $user = $this->createOrUpdateUserAction->execute($userDTO);

        return new GenericResource($user);
    }

    public function show($id)
    {
        return new GenericResource($this->find(new User(), $id));
    }

    public function destroy()
    {
        User::destroy($this->getIds('user'));

        return response(null, 204);
    }
}
