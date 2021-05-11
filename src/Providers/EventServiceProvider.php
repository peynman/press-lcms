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
        // sync ecommerce for support group
        'Larapress\Profiles\Services\FormEntry\FormEntryUpdateEvent' => [
            'Larapress\LCMS\Services\SupportGroup\SupportGroupFormListener',
        ],
        'Larapress\Auth\Signup\SignupEvent' => [
            'Larapress\LCMS\Services\SupportGroup\SupportGroupSignupListener'
        ],

        // on cart purchased, calculate support share & introducer share
        'Larapress\ECommerce\Services\Cart\CartPurchasedEvent' => [
            'Larapress\LCMS\Services\SupportGroup\CartPurchasedSupportShare',
            'Larapress\LCMS\Services\SupportGroup\CartPurchasedIntroducerShare',
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
