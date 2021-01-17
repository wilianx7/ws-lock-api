<?php

namespace App\DTOs;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;

class UserDTO extends DataTransferObject
{
    public ?int $id;
    public string $name;
    public string $email;
    public string $login;
    public ?string $password;

    public static function fromCollection(Collection $data): self
    {
        return new self([
            'id' => intval($data->get('id')) ?: null,
            'name' => $data->get('name'),
            'email' => $data->get('email'),
            'login' => $data->get('login'),
            'password' => $data->get('password'),
        ]);
    }
}
