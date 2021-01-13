<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected function getIds(string $resourceName): array
    {
        $ids = request()->route()->parameter($resourceName);

        return explode(',', $ids);
    }

    protected function find($model, string $id): Model
    {
        $include = $this->getInclude();

        if (reset($include)) {
            $includes = collect($this->getInclude());

            return $model->with($includes->toArray())->find($id);
        } else {
            return $model->find($id);
        }
    }

    private function getInclude(): array
    {
        return explode(',', request()->get('include') ?? '');
    }
}
