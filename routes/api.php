<?php

use Illuminate\Support\Facades\Route;
use Larapress\LCMS\Services\CourseSession\FormsController;
use Larapress\LCMS\Services\SupportGroup\SupportGroupController;

// api routes with public access
Route::middleware(config('larapress.pages.middlewares'))
    ->prefix(config('larapress.crud.prefix'))
    ->group(function () {
        FormsController::registerPublicApiRoutes();
    });

Route::middleware(config('larapress.crud.middlewares'))
    ->prefix(config('larapress.crud.prefix'))
    ->group(function () {
        SupportGroupController::registerRoutes();
    });
