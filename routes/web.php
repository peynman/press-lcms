<?php

use Illuminate\Support\Facades\Route;
use Larapress\LCMS\Services\CourseSession\ContentController;
use Larapress\LCMS\Services\CourseSession\FormsController;

Route::middleware(config('larapress.crud.public-middlewares'))
    ->prefix(config('larapress.crud.prefix'))
    ->group(function () {
        FormsController::registerWebRoutes();
        ContentController::registerWebRoutes();
    });
