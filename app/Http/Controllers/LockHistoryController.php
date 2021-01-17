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
        $lockHistories = QueryBuilder::for(LockHistory::class)
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
            ->with($request->input('with') ?? [])
            ->getQuery();

        return new GenericResourceCollection($lockHistories);
    }
}
