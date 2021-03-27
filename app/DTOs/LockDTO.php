<?php

namespace App\DTOs;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;

class LockDTO extends DataTransferObject
{
    public ?int $id;
    public string $name;
    public ?string $mac_address;
    public Collection $users;

    public static function fromCollection(Collection $data): self
    {
        return new self([
            'id' => intval($data->get('id')) ?: null,
            'name' => $data->get('name'),
            'mac_address' => $data->get('mac_address'),
            'users' => collect($data->get('users')),
        ]);
    }
}
