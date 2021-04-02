<?php

namespace App\Http\Controllers;

use App\Http\Resources\GenericResourceCollection;
use App\Models\LockHistory;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LockHistoryController extends Controller
{
    public function index(Request $request)
    {
        $withRelations = $request->input('with_relations')
            ? explode(',', $request->input('with_relations'))
            : [];

        $lockHistories = QueryBuilder::for(LockHistory::whereBelongsToUserLocks())
            ->orderBy('created_at', 'DESC')
            ->allowedFilters([
                'description',
                AllowedFilter::exact('id'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('lock_id'),
                AllowedFilter::scope('term', 'whereTerm'),
            ])
            ->allowedIncludes([
                'user',
                'lock',
            ])
            ->with($withRelations);

        return new GenericResourceCollection($lockHistories);
    }
}
