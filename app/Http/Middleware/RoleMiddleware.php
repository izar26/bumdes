<?php

namespace App\Http\Middleware;

/**
 * Lightweight RoleMiddleware wrapper.
 *
 * The project uses Spatie's permission package. To avoid PSR-4 class/path conflicts
 * we provide a small local wrapper that extends Spatie's middleware. This keeps
 * any route/kernel references to App\Http\Middleware\RoleMiddleware working.
 */
class RoleMiddleware extends \Spatie\Permission\Middleware\RoleMiddleware
{
    // Intentionally empty — inherit behavior from Spatie package.
}
