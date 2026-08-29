<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends \Illuminate\Routing\Controller
{
    use AuthorizesRequests;

    public function callAction($method, $parameters)
    {
        unset($parameters['tenant_slug']);

        return parent::callAction($method, $parameters);
    }
}
