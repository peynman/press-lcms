<?php

namespace Larapress\LCMS\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // on cart purchased, calculate support share & introducer share
        'Larapress\ECommerce\Services\Cart\CartPurchasedEvent' => [
            'Larapress\LCMS\Services\SupportGroup\Reports\CartPurchasedSupportReport',
            'Larapress\LCMS\Services\SupportGroup\Reports\CartPurchasedIntroducerReport',
        ],

        // gift for user profile
        'Larapress\Profiles\Services\FormEntry\FormEntryUpdateEvent' => [
            'Larapress\LCMS\Services\SupportGroup\Listeners\ProfileFilledListener',
        ],

        // gift for signup
        'Larapress\Auth\Signup\SignupEvent' => [
            // Introducer on signin
            'Larapress\LCMS\Services\SupportGroup\Listeners\SignupGiftListener'
        ],

    ];


    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
