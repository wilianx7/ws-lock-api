<?php

namespace App\Base;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;
use function app;

abstract class BaseResourceCollection extends ResourceCollection
{
    private ?Request $request;

    public function __construct(Builder $resource)
    {
        $this->request = app()->make(Request::class);

//            $resourceWithRelations = $this->loadRelations($resource);

        parent::__construct([$resource->get()]);
    }

    public function toArray($request): array
    {
        return [
            'data' => $this->collection->toArray()
        ];
    }

//    private function loadRelations(Builder $resource): Builder
//    {
//        $baseQuery = $resource;
//
//        return $resource->withRelations($this->request->get('with'))
//            ->addNestedWhereQuery($baseQuery->getQuery());
//    }
}
