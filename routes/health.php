<?php

use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;

Router::get('/up', function () {
    return new JsonResponse(['status' => 'ok'], 200, [], JSON_UNESCAPED_SLASHES);
});
