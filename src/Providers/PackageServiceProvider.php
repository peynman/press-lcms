<?php

namespace Larapress\LCMS\Providers;

use Illuminate\Support\ServiceProvider;
use Larapress\LCMS\Services\SupportGroup\ISupportGroupService;
use Larapress\LCMS\Commands\LCMSCreateProductType;
use Larapress\LCMS\Services\CourseSession\FormService;
use Larapress\LCMS\Services\CourseSession\Repository;
use Larapress\LCMS\Services\CourseSession\ICourseSessionFormService;
use Larapress\LCMS\Services\CourseSession\ICourseSessionRepository;
use Larapress\LCMS\Services\SupportGroup\SupportGroupService;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ICourseSessionFormService::class, FormService::class);
        $this->app->bind(ICourseSessionRepository::class, Repository::class);
        $this->app->bind(ISupportGroupService::class, SupportGroupService::class);

        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'larapress');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        $this->publishes([
            __DIR__.'/../../config/lcms.php' => config_path('larapress/lcms.php'),
        ], ['config', 'larapress', 'larapress-lcms']);

        if ($this->app->runningInConsole()) {
            $this->commands([
                LCMSCreateProductType::class,
            ]);
        }
    }
}
