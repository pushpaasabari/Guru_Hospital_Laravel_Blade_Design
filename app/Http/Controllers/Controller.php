<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;

abstract class Controller
{
    use AuthorizesRequests;

    public function authorize($ability, $arguments = [])
    {
        return Gate::authorize($ability, $arguments);
    }
}
