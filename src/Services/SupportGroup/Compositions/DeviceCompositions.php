<?php

namespace Larapress\LCMS\Services\SupportGroup\Compositions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Services\CRUD\CRUDProviderComposition;
use Larapress\ECommerce\Models\Cart;
use Larapress\LCMS\Services\SupportGroup\ISupportGroupUser;
use Larapress\Profiles\IProfileUser;

class DeviceComposition extends CRUDProviderComposition
{
    /**
     * Undocumented function
     *
     * @param Builder $query
     * @return Builder
     */
    public function onBeforeQuery(Builder $query): Builder
    {
        $query = parent::onBeforeQuery($query);

        /** @var IProfileUser $user */
        $user = Auth::user();
        if (!$user->hasRole(config('larapress.profiles.security.roles.super_role'))) {
            $query->orWhereHas('user.form_entries', function ($q) use ($user) {
                $q->where('tags', 'support-group-' . $user->id);
            });
        }

        return $query;
    }

    /**
     * @param Cart $object
     *
     * @return bool
     */
    public function onBeforeAccess($object): bool
    {
        /** @var IProfileUser $user */
        $user = Auth::user();

        $access = false;
        if (!$user->hasRole(config('larapress.profiles.security.roles.super_role'))) {
            /** @var ISupportGroupUser */
            $customer = $object->user;
            $access = $customer?->getSupportUserId() === $user->id;
        }

        return parent::onBeforeAccess($object) || $access;
    }
}
