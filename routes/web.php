<?php

use Illuminate\Support\Facades\Route;
use Larapress\LCMS\Services\CourseSession\ContentController;
use Larapress\LCMS\Services\CourseSession\FormsController;

Route::middleware(config('larapress.pages.middleware'))
    ->prefix(config('larapress.pages.prefix'))
    ->group(function () {
        FormsController::registerWebRoutes();
        ContentController::registerWebRoutes();
    });
