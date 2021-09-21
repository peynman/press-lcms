<?php

namespace Larapress\LCMS\Services\SupportGroup;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Extend\Helpers;
use Larapress\ECommerce\IECommerceUser;
use Larapress\ECommerce\Models\WalletTransaction;
use Larapress\ECommerce\Services\Wallet\IWalletService;
use Larapress\LCMS\Services\SupportGroup\Requests\MySupportGroupUpdateRequest;
use Larapress\LCMS\Services\SupportGroup\Requests\SupportGroupUpdateRequest;
use Larapress\Profiles\Models\FormEntry;
use Larapress\Profiles\Services\FormEntry\IFormEntryService;
use Larapress\Profiles\IProfileUser;

class SupportGroupService implements ISupportGroupService
{

    /** @var IFormEntryService */
    protected $formService;

    /** @var IWalletService */
    protected $walletService;

    public function __construct(IFormEntryService $formService, IWalletService $walletService)
    {
        $this->formService = $formService;
        $this->walletService = $walletService;
    }

    /**
     * Undocumented function
     *
     * @param SupportGroupUpdateRequest $request
     * @return Response
     */
    public function updateUsersSupportGroup(SupportGroupUpdateRequest $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 0);

        $class = config('larapress.crud.user.model');

        $avSupportUserIds = [];
        $supportUserId = null;
        $supportProfile = null;
        if ($request->shouldRandomizeSupportIds()) {
            $avSupportUserIds = call_user_func([$class, 'whereHas'], 'roles', function ($q) {
                $q->whereIn('id', config('larapress.lcms.support_randomizer_role_ids'));
            })->get();
        } else {
            $supportUserId = $request->getSupportUserID();
            $supportUser = call_user_func([$class, 'find'], $supportUserId);
            $supportProfile = !is_null($supportUser->profile) ? $supportUser->profile['data']['values'] : [];
            if (!$supportUser->hasRole(config('larapress.lcms.support_role_ids'))) {
                throw new AppException(AppException::ERR_INVALID_QUERY);
            }
        }

        if ($request->shouldUseAllNoneSupportUsers()) {
            $userIds = User::whereDoesntHave('form_entries', function ($q) {
                $q->where('tags', 'LIKE', 'support-group-%');
            })->whereHas('roles', function ($q) {
                $q->whereIn('id', config('larapress.lcms.customer_role_ids'));
            });
        } else {
            $userIds = $request->getUserIds();
        }

        $totalSupUserIds = count($avSupportUserIds);
        $indexer = 1;

        $updateUsersSupportGroup = function ($userIds) use (&$indexer, $request, $avSupportUserIds, $totalSupUserIds, $class, $supportUserId, $supportProfile) {
            foreach ($userIds as $userId) {
                if ($request->shouldRandomizeSupportIds()) {
                    $supportUser = $avSupportUserIds[$indexer % $totalSupUserIds];
                    $supportUserId = $supportUser->id;
                    $supportProfile = !is_null($supportUser->profile) ? $supportUser->profile['data']['values'] : [];
                }

                if (is_numeric($userId)) {
                    $user = call_user_func([$class, 'find'], $userId);
                } else {
                    $user = $userId;
                    $userId = $user->id;
                }

                $this->formService->updateUserFormEntryTag(
                    $request,
                    $user,
                    config('larapress.lcms.support_group_default_form_id'),
                    'support-group-' . $supportUserId,
                    function ($request, $inputNames, $form, $entry) use ($supportUserId, $supportProfile) {
                        return $this->getSupportIdsDataForEntry($entry, $supportUserId, $supportProfile);
                    }
                );
                Helpers::forgetCachedValues(['user.support:' . $userId]);

                $indexer++;
            }
        };

        if (is_array($userIds)) {
            $updateUsersSupportGroup($userIds);
        } else {
            $userIds->chunk(100, $updateUsersSupportGroup);
        }

        return [
            'message' => trans('larapress::lcms.support_groups_updated'),
        ];
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @param IECommerceUser $user
     * @param IECommerceUser|int $supportUser
     *
     * @return Response
     */
    public function updateUserSupportGroup(Request $request, IECommerceUser $user, $supportUser)
    {
        $class = config('larapress.crud.user.model');
        if (is_numeric($supportUser)) {
            $supportUser = call_user_func([$class, 'find'], $supportUser);
        }
        if (is_null($supportUser)) {
            throw new AppException(AppException::ERR_INVALID_QUERY);
        }
        $supportUserId = $supportUser->id;
        $supportProfile = is_null($supportUser->profile) ? [] : $supportUser->profile['data']['values'];
        if (!$supportUser->hasRole(config('larapress.lcms.support_role_ids'))) {
            throw new AppException(AppException::ERR_INVALID_QUERY);
        }

        $this->formService->updateUserFormEntryTag(
            $request,
            $user,
            config('larapress.lcms.support_group_default_form_id'),
            'support-group-' . $supportUserId,
            function ($request, $inputNames, $form, $entry) use ($supportUserId, $supportProfile) {
                return $this->getSupportIdsDataForEntry($entry, $supportUserId, $supportProfile);
            }
        );

        Helpers::forgetCachedValues(['user.support:' . $user->id]);

        return [
            'message' => trans('larapress::lcms.support_groups_updated'),
            'support' => $supportProfile
        ];
    }

    /**
     * Undocumented function
     *
     * @param MySupportGroupUpdateRequest $request
     * @param int|IProfileUser $supportUser
     * @return Response
     */
    public function updateMySupportGroup(MySupportGroupUpdateRequest $request, $supportUser)
    {
        $class = config('larapress.crud.user.model');
        if (is_numeric($supportUser)) {
            $supportUser = call_user_func([$class, 'find'], $supportUser);
        }
        if (is_null($supportUser)) {
            throw new AppException(AppException::ERR_INVALID_QUERY);
        }

        /** @var IECommerceUser */
        $user = Auth::user();
        if (is_null($supportUser)) {
            throw new AppException(AppException::ERR_INVALID_QUERY);
        }
        $supportUserId = $supportUser->id;
        $supportProfile = is_null($supportUser->profile) ? [] : $supportUser->profile['data']['values'];
        if (!$supportUser->hasRole(config('larapress.lcms.support_role_ids'))) {
            throw new AppException(AppException::ERR_INVALID_QUERY);
        }

        $this->formService->updateUserFormEntryTag(
            $request,
            $user,
            config('larapress.lcms.support_group_default_form_id'),
            'support-group-' . $supportUserId,
            function ($request, $inputNames, $form, $entry) use ($user, $supportUser, $supportUserId, $supportProfile) {
                if (is_null($entry)) {
                    $this->giftUserForSupportGroupRegistration($user, $supportUser);
                }
                return $this->getSupportIdsDataForEntry($entry, $supportUserId, $supportProfile);
            }
        );

        Helpers::forgetCachedValues(['user.support:' . $user->id]);

        return [
            'message' => trans('larapress::lcms.support_groups_updated'),
            'support' => $supportProfile
        ];
    }

    /**
     * Undocumented function
     *
     * @param IECommerceUser $user
     * @param IECommerceUser $supportUser
     *
     * @return void
     */
    public function giftUserForSupportGroupRegistration(IECommerceUser $user, IECommerceUser $supportUser)
    {
        // default gift amount
        $giftAmount = config('larapress.lcms.gifts.introducers_gift.amount', 0);
        $currency = config('larapress.lcms.gifts.introducers_gift.currency');

        if (is_null($currency) || is_null($giftAmount)) {
            return;
        }

        // update default gift amount if support introducer has customized gift
        $supportSettings = FormEntry::query()
            ->where('form_id', config('larapress.ecommerce.lms.support_settings_default_form_id'))
            ->where('user_id', $supportUser->id)
            ->first();
        if (!is_null($supportSettings)) {
            if (isset($supportSettings->data['values']['register_gift'])) {
                $customGift = floatval($supportSettings->data['values']['register_gift']);
                if ($customGift > 0) {
                    $giftAmount = $customGift;
                }
            }
        }

        $userPrevGifts = $this->walletService->getUserTotalAquiredGiftBalance($user, $currency);
        $giftAmount = $giftAmount - $userPrevGifts;

        if ($giftAmount > 0) {
            $this->walletService->addBalanceForUser(
                $user,
                $giftAmount,
                config('larapress.profiles.gifts.introducers_gift.currency'),
                WalletTransaction::TYPE_VIRTUAL_MONEY,
                WalletTransaction::FLAGS_REGISTRATION_GIFT,
                trans('larapress::lcms.introducer_gift_wallet_desc', [
                    'introducer_id' => $supportUser->id
                ]),
                []
            );
        }
    }


    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     *
     * @return FormEntry[]
     */
    public function getIntroducedUsersList($user)
    {
        $introduced = FormEntry::query()
            ->where('form_id', config('larapress.lcms.introducer_default_form_id'))
            ->where('tags', 'introducer-id-' . $user->id)
            ->get();

        // protect form filler personal info!
        foreach ($introduced as &$user) {
            $data = $user->data;
            $data['ip'] = null;
            $data['agent'] = null;
            $user->data = $data;
        }

        return $introduced;
    }

    /**
     * @return array
     */
    protected function getSupportIdsDataForEntry($entry, $supportUserId, $supportProfile)
    {
        $values = [];
        if (is_null($entry) || !isset($entry->data['values']['support_ids'])) {
            $values['support_ids'] = [];
        } else {
            $values['support_ids'] = array_values($entry->data['values']['support_ids']);
        }

        $values['support_ids'][] = [
            'support_user_id' => $supportUserId,
            'support_name' => isset($supportProfile['firstname']) && isset($supportProfile['lastname']) ?
                $supportProfile['firstname'] . ' ' . $supportProfile['lastname'] : 'support-id-' . $supportUserId,
            'updated_at' => Carbon::now(),
        ];
        return $values;
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     *
     * @return void
     */
    public function resetSupportGroupCache($user)
    {
        Helpers::forgetCachedValues(['user.support:' . $user->id]);
    }
}
