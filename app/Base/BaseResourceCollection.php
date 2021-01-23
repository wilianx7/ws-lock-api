<?php

namespace App\Base;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;
use function app;

abstract class BaseResourceCollection extends ResourceCollection
{
    private ?Request $request;

    public function __construct($resource)
    {
        $this->request = app()->make(Request::class);

        parent::__construct($resource->get());
    }

    public function toArray($request): array
    {
        return [
            'data' => $this->collection->toArray()
        ];
    }
}
