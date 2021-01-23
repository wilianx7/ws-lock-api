<?php

namespace App\Base;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use function method_exists;

abstract class BaseResource extends JsonResource
{
    public ?Request $request = null;

    public function __construct($resource)
    {
        $this->request = app()->make(Request::class);

        if ($resource && method_exists($resource, 'fresh') && $this->request->get('with')) {
            $resource = $resource->fresh([$this->request->get('with')]);
        }

        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
