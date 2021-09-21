<?php

namespace Larapress\LCMS\Services\SupportGroup\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Larapress\Auth\Signup\SignupEvent;
use Larapress\ECommerce\Models\WalletTransaction;
use Larapress\ECommerce\Services\Wallet\IWalletService;
use Larapress\LCMS\Services\SupportGroup\ISupportGroupService;
use Larapress\Profiles\IProfileUser;
use Larapress\Profiles\Services\FormEntry\IFormEntryService;

/**
 * Fill introducer form if user has registered with one.
 * and give appropriate gifts
 */
class SignupGiftListener implements ShouldQueue
{
    use Dispatchable;

    /** @var IFormEntryService */
    protected $formService;
    /** @var IWalletService */
    protected $walletService;
    /** @var ISupportGroupService */
    protected $suppService;

    public function __construct(IFormEntryService $formService, IWalletService $walletService, ISupportGroupService $suppService)
    {
        $this->formService = $formService;
        $this->walletService = $walletService;
        $this->suppService = $suppService;
    }

    /**
     * Undocumented function
     *
     * @param SignupEvent $event
     *
     * @return void
     */
    public function handle(SignupEvent $event)
    {
        ini_set('max_execution_time', 0);

        $user = $event->getUser();

        if (!is_null($event->getIntroducerID())) {
            $class = config('larapress.crud.user.model');
            /** @var IProfileUser */
            $introducer = call_user_func([$class, 'find'], $event->getIntroducerID());
            if (!is_null($introducer)) {
                $this->giftWithIntoruder($user, $introducer);
                return;
            }
        }

        $this->giftWithoutIntroducer($user);
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @return void
     */
    protected function giftWithoutIntroducer(IProfileUser $user)
    {
        $giftAmount = config('larapress.lcms.gifts.registeration_gift.amount', 0);
        $currency = config('larapress.lcms.gifts.registeration_gift.currency');

        if (is_null($currency) || is_null($giftAmount)) {
            return;
        }

        $userPrevGifts = $this->walletService->getUserTotalAquiredGiftBalance($user, $currency);
        $giftAmount = $giftAmount - $userPrevGifts;

        if ($giftAmount > 0) {
            $this->walletService->addBalanceForUser(
                $user,
                $giftAmount,
                $currency,
                WalletTransaction::TYPE_VIRTUAL_MONEY,
                WalletTransaction::FLAGS_REGISTRATION_GIFT,
                trans('larapress::lcms.register_gift_wallet_desc'),
                []
            );
        }
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @param IProfileUser $introducer
     *
     * @return void
     */
    protected function giftWithIntoruder(IProfileUser $user, IProfileUser $introducer)
    {
        // default gift amount
        $giftAmount = config('larapress.lcms.gifts.introducers_gift.amount', 0);
        $currency = config('larapress.lcms.gifts.introducers_gift.currency');

        // introducer is support group user
        if ($introducer->hasRole(config('larapress.ecommerce.lms.support_role_ids'))) {
            $this->formService->updateUserFormEntryTag(
                null,
                $user,
                config('larapress.ecommerce.lms.support_group_default_form_id'),
                'support-group-' . $introducer->id,
                function ($request, $inputNames, $form, $entry) use ($introducer) {
                    $values = [
                        'support_user_id' => is_null($entry) || !isset($entry->data['values']['support_user_id']) ? [$introducer->id] :
                            array_merge($entry->data['values']['support_user_id'], [$introducer->id])
                    ];
                    return $values;
                }
            );

            $this->suppService->giftUserForSupportGroupRegistration($user, $introducer);
        } else {
            // introducer is another customer
            $this->formService->updateUserFormEntryTag(
                null,
                $user,
                config('larapress.lcms.introducer_default_form_id'),
                'introducer-id-' . $introducer->id,
                function ($req, $form, $entry) use ($introducer) {
                    return [
                        'introducer_id' => $introducer->id,
                    ];
                }
            );

            if (is_null($currency) || is_null($giftAmount)) {
                return;
            }

            $userPrevGifts = $this->walletService->getUserTotalAquiredGiftBalance($user, $currency);
            $giftAmount = $giftAmount - $userPrevGifts;

            if ($giftAmount > 0) {
                $this->walletService->addBalanceForUser(
                    $user,
                    $giftAmount,
                    $currency,
                    WalletTransaction::TYPE_VIRTUAL_MONEY,
                    WalletTransaction::FLAGS_REGISTRATION_GIFT,
                    trans('larapress::lcms.introducer_gift_wallet_desc', [
                        'introducer_id' => $introducer->id
                    ]),
                    []
                );
            }
        }
    }
}
