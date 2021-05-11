<?php

namespace Larapress\LCMS\Services\SupportGroup;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Larapress\ECommerce\Models\WalletTransaction;
use Larapress\ECommerce\Services\Wallet\IWalletService;
use Larapress\Profiles\Services\FormEntry\FormEntryUpdateEvent;

class SupportGroupFormListener implements ShouldQueue
{
    use Dispatchable;

    public function handle(FormEntryUpdateEvent $event)
    {
        switch ($event->formId) {
                // user profile updated
            case config('larapress.profiles.default_profile_form_id'):
                if ($event->created) {
                    if (
                        !is_null(config('larapress.ecommerce.banking.profle_gift.amount')) &&
                        !is_null(config('larapress.ecommerce.banking.profle_gift.currency')) &&
                        config('larapress.ecommerce.banking.profle_gift.amount') > 0
                    ) {
                        // add profile completion gift
                        /** @var IWalletService */
                        $bankingService = app(IWalletService::class);
                        $bankingService->addBalanceForUser(
                            $event->getUser(),
                            config('larapress.ecommerce.banking.profle_gift.amount'),
                            config('larapress.ecommerce.banking.profle_gift.currency'),
                            WalletTransaction::TYPE_VIRTUAL_MONEY,
                            WalletTransaction::FLAGS_REGISTRATION_GIFT,
                            trans('larapress::ecommerce.banking.messages.wallet-descriptions.profile_gift_wallet_desc')
                        );
                    }
                }
                break;
        }
    }
}
