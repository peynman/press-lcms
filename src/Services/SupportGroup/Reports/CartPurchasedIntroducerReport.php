<?php

namespace Larapress\LCMS\Services\SupportGroup\Reports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Larapress\ECommerce\Services\Cart\CartPurchasedEvent;

class CartPurchasedIntroducerReport implements ShouldQueue
{
    use Dispatchable;

    public function handle(CartPurchasedEvent $event)
    {
    }
}
