<?php

namespace Larapress\LCMS\Services\SupportGroup\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Larapress\ECommerce\Models\WalletTransaction;
use Larapress\ECommerce\Services\Wallet\IWalletService;
use Larapress\Profiles\Services\FormEntry\FormEntryUpdateEvent;

class ProfileFilledListener implements ShouldQueue
{
    use Dispatchable;

    public function handle(FormEntryUpdateEvent $event)
    {
        if ($event->formId === config('larapress.profiles.default_profile_form_id')) {
            if ($event->created) {
                $user = $event->getUser();
                // default gift amount
                $giftAmount = config('larapress.lcms.gifts.profle_gift.amount', 0);
                $currency = config('larapress.lcms.gifts.profle_gift.currency');

                if (is_null($currency) || is_null($giftAmount)) {
                    return;
                }

                /** @var IWalletService */
                $walletService = app(IWalletService::class);

                $userPrevGifts = $walletService->getUserTotalAquiredGiftBalance($user, $currency);
                $giftAmount = $giftAmount - $userPrevGifts;

                if ($giftAmount > 0) {
                    // add profile completion gift
                    /** @var IWalletService */
                    $walletService->addBalanceForUser(
                        $user,
                        $giftAmount,
                        $currency,
                        WalletTransaction::TYPE_VIRTUAL_MONEY,
                        WalletTransaction::FLAGS_REGISTRATION_GIFT,
                        trans('larapress::lcms..profile_gift_wallet_desc'),
                        []
                    );
                }
            }
        }
    }
}
