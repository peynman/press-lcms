<?php

namespace Larapress\LCMS\Services\SupportGroup;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\ECommerce\IECommerceUser;
use Larapress\ECommerce\Models\WalletTransaction;
use Larapress\ECommerce\Services\Banking\IBankingService;
use Larapress\Profiles\Models\FormEntry;
use Larapress\Profiles\Services\FormEntry\IFormEntryService;
use Larapress\Profiles\IProfileUser;

class SupportGroupService implements ISupportGroupService
{

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

        $class = config('larapress.crud.user.class');

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
            if (!$supportUser->hasRole(config('larapress.lcms.support_role_id'))) {
                throw new AppException(AppException::ERR_INVALID_QUERY);
            }
        }

        if ($request->shouldUseAllNoneSupportUsers()) {
            $userIds = User::whereDoesntHave('form_entries', function ($q) {
                $q->where('tags', 'LIKE', 'support-group-%');
            })->whereHas('roles', function ($q) {
                $q->where('id', config('larapress.profiles.customer_role_id'));
            });
        } else {
            $userIds = $request->getUserIds();
        }

        /** @var IFormEntryService */
        $service = app(IFormEntryService::class);

        $totalSupUserIds = count($avSupportUserIds);
        $indexer = 1;

        $updateUsersSupportGroup = function ($userIds) use (&$indexer, $request, $avSupportUserIds, $totalSupUserIds, $class, $service, $supportUserId, $supportProfile) {
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

                $service->updateUserFormEntryTag(
                    $request,
                    $user,
                    config('larapress.lcms.support_group_default_form_id'),
                    'support-group-' . $supportUserId,
                    function ($request, $inputNames, $form, $entry) use ($supportUserId, $supportProfile) {
                        return $this->getSupportIdsDataForEntry($entry, $supportUserId, $supportProfile);
                    }
                );
                Cache::tags(['user.support:' . $userId])->flush();

                $indexer++;
            }
        };
        if (is_array($userIds)) {
            $updateUsersSupportGroup($userIds);
        } else {
            $userIds->chunk(100, $updateUsersSupportGroup);
        }
        return ['message' => 'Success'];
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @param IECommerceUser $user
     * @param IECommerceUser|int $supportUser
     * @return Response
     */
    public function updateUserSupportGroup(Request $request, IECommerceUser $user, $supportUser)
    {
        /** @var IFormEntryService */
        $service = app(IFormEntryService::class);

        $class = config('larapress.crud.user.class');
        if (is_numeric($supportUser)) {
            $supportUser = call_user_func([$class, 'find'], $supportUser);
        }
        if (is_null($supportUser)) {
            throw new AppException(AppException::ERR_INVALID_QUERY);
        }
        $supportUserId = $supportUser->id;
        $supportProfile = is_null($supportUser->profile) ? [] : $supportUser->profile['data']['values'];
        if (!$supportUser->hasRole(config('larapress.lcms.support_role_id'))) {
            throw new AppException(AppException::ERR_INVALID_QUERY);
        }

        $service->updateUserFormEntryTag(
            $request,
            $user,
            config('larapress.lcms.support_group_default_form_id'),
            'support-group-' . $supportUserId,
            function ($request, $inputNames, $form, $entry) use ($supportUserId, $supportProfile) {
                return $this->getSupportIdsDataForEntry($entry, $supportUserId, $supportProfile);
            }
        );
        Cache::tags(['user.support:' . $user->id])->flush();
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @param int|IProfileUser $supportUser
     * @return Response
     */
    public function updateMySupportGroup(Request $request, $supportUser)
    {
        /** @var IFormEntryService */
        $service = app(IFormEntryService::class);

        $class = config('larapress.crud.user.class');
        if (is_numeric($supportUser)) {
            $supportUser = call_user_func([$class, 'find'], $supportUser);
        }
        if (is_null($supportUser)) {
            throw new AppException(AppException::ERR_INVALID_QUERY);
        }
        $supportUserId = $supportUser->id;
        $supportProfile = is_null($supportUser->profile) ? [] : $supportUser->profile['data']['values'];
        if (!$supportUser->hasRole(config('larapress.lcms.support_role_id'))) {
            throw new AppException(AppException::ERR_INVALID_QUERY);
        }

        $service->updateUserFormEntryTag(
            $request,
            Auth::user(),
            config('larapress.lcms.support_group_default_form_id'),
            'support-group-' . $supportUserId,
            function ($request, $inputNames, $form, $entry) use ($supportUserId, $supportProfile, $supportUser) {
                $data = $this->getSupportIdsDataForEntry($entry, $supportUserId, $supportProfile);
                if (is_null($entry)) {
                    /** @var IECommerceUser */
                    $user = Auth::user();
                    $this->updateUserRegistrationGiftWithIntroducer($user, $supportUser, false, false);
                }
                return $data;
            }
        );

        return [
            'message' => trans('larapress::lcms.support_group_update_success'),
            'support' => $supportProfile
        ];
    }

    /**
     * add user to support/introducer group, if we have introducer
     * add user gift balance too
     *
     * @param IProfileUser $user
     * @param int|IProfileUser $introducer
     * @param bool $updateSupportGroup
     * @param bool $updateIntroducer
     * @return void
     */
    public function updateUserRegistrationGiftWithIntroducer(IProfileUser $user, $introducer, $updateSupportGroup, $updateIntroducer)
    {
        // add registerar gift based on introducer id
        if (!is_null($introducer)) {
            // add to support group if introducer has support role
            $class = config('larapress.crud.user.class');
            if (is_numeric($introducer)) {
                $introducer = call_user_func([$class, 'find'], $introducer);
            }

            /** @var IFormEntryService */
            $service = app(IFormEntryService::class);

            $service->updateUserFormEntryTag(
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

            // default gift amount
            $giftAmount = config('larapress.lcms.introducers.user_gift.amount');

            if ($introducer->hasRole(config('larapress.ecommerce.lms.support_role_id'))) {
                if ($updateSupportGroup) {
                    $service->updateUserFormEntryTag(
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
                }

                // update default gift amount if support introducer has customized gift
                $supportSettings = FormEntry::query()
                    ->where('form_id', config('larapress.ecommerce.lms.support_settings_default_form_id'))
                    ->where('user_id', $introducer->id)
                    ->first();
                if (!is_null($supportSettings)) {
                    if (isset($supportSettings->data['values']['register_gift'])) {
                        $customGift = floatval($supportSettings->data['values']['register_gift']);
                        if ($customGift > 0) {
                            $giftAmount = $customGift;
                        }
                    }
                }
            }


            /** @var IBankingService */
            $bankService = app(IBankingService::class);

            $userPrevGifts = $bankService->getUserTotalAquiredGiftBalance($user, config('larapress.ecommerce.banking.registeration_gift.currency'));
            $giftAmount = $giftAmount - $userPrevGifts;

            if ($giftAmount > 0) {
                $bankService->addBalanceForUser(
                    $user,
                    $giftAmount,
                    config('larapress.ecommerce.lms.introducers.user_gift.currency'),
                    WalletTransaction::TYPE_VIRTUAL_MONEY,
                    WalletTransaction::FLAGS_REGISTRATION_GIFT,
                    trans('larapress::ecommerce.banking.messages.wallet-descriptions.introducer_gift_wallet_desc', [
                        'introducer_id' => $introducer->id
                    ])
                );
            }
        }
        // add global registration gift for user
        else {
            if (
                !is_null(config('larapress.ecommerce.banking.registeration_gift.amount')) && !is_null(config('larapress.ecommerce.banking.registeration_gift.currency')) &&
                config('larapress.ecommerce.banking.registeration_gift.amount') > 0 && config('larapress.ecommerce.banking.registeration_gift.currency') > 0
            ) {
                /** @var IBankingService */
                $bankService = app(IBankingService::class);
                $bankService->addBalanceForUser(
                    $user,
                    config('larapress.ecommerce.lms.registeration_gift.amount'),
                    config('larapress.ecommerce.lms.registeration_gift.currency'),
                    WalletTransaction::TYPE_VIRTUAL_MONEY,
                    WalletTransaction::FLAGS_REGISTRATION_GIFT,
                    trans('larapress::ecommerce.banking.messages.wallet-descriptions.register_gift_wallet_desc')
                );
            }
        }
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
}
