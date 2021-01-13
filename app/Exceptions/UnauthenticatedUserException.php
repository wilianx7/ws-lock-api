<?php

namespace App\Exceptions;

use Exception;

class UnauthenticatedUserException extends Exception
{
    public function __construct()
    {
        parent::__construct("Unauthenticated user!");
    }
}
