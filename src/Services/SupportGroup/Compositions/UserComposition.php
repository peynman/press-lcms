<?php

namespace Larapress\LCMS\Services\SupportGroup\Compositions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Services\CRUD\CRUDProviderComposition;
use Larapress\LCMS\Services\SupportGroup\ISupportGroupUser;
use Larapress\Profiles\IProfileUser;

class UserComposition extends CRUDProviderComposition
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
            if ($user->hasRole(config('larapress.lcms.support_role_ids'))) {
                $query->whereHas('form_entries', function ($q) use ($user) {
                    $q->where('tags', 'support-group-' . $user->id);
                });
            }
        }

        return $query;
    }

    /**
     * @param ISupportGroupUser $object
     *
     * @return bool
     */
    public function onBeforeAccess($object): bool
    {
        /** @var IProfileUser $user */
        $user = Auth::user();

        $access = false;
        if (!$user->hasRole(config('larapress.profiles.security.roles.super_role'))) {
            $access = $object->getSupportUserId() === $user->id;
        }

        return parent::onBeforeAccess($object) || $access;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getValidRelations(): array
    {
        return array_merge(parent::getValidRelations(), [
            'form_support_user_profile' => config('larapress.profiles.routes.form_entries.provider'),
            'form_profile_support' => config('larapress.profiles.routes.form_entries.provider'),
            'form_support_registration_entry' => config('larapress.profiles.routes.form_entries.provider'),
        ]);
    }
}
