<?php

namespace Larapress\LCMS\Services\SupportGroup\Compositions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\Services\CRUD\CRUDProviderComposition;
use Larapress\LCMS\Services\SupportGroup\ISupportGroupUser;
use Larapress\Profiles\Models\FormEntry;
use Larapress\ECommerce\IECommerceUser;

class FormEntryComposition extends CRUDProviderComposition
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

        /** @var IECommerceUser $user */
        $user = Auth::user();
        if (!$user->hasRole(config('larapress.profiles.security.roles.super_role'))) {
            if ($user->hasRole(config('larapress.lcms.support_role_ids'))) {
                $query->whereHas('user.form_entries', function ($q) use ($user) {
                    $q->where('tags', 'support-group-' . $user->id);
                });
            } elseif ($user->hasRole(config('larapress.ecommerce.product_owner_role_ids'))) {
                $ownerTagEntries = Helpers::flattenNestedAray(
                    array_map(function ($id) {
                        return ['course-' . $id . '-presence', 'course-' . $id . '-taklif', 'azmoon-' . $id];
                    }, $user->getOwenedProductsIds())
                );
                $query->whereIn('tags', $ownerTagEntries);
            }
        }

        return $query;
    }

    /**
     * @param FormEntry $object
     *
     * @return bool
     */
    public function onBeforeAccess($object): bool
    {
        /** @var IECommerceUser $user */
        $user = Auth::user();

        $access = false;
        if (!$user->hasRole(config('larapress.profiles.security.roles.super_role'))) {
            if ($user->hasRole(config('larapress.lcms.support_role_ids'))) {
                /** @var ISupportGroupUser */
                $customer = $object->user;
                $access = $customer?->getSupportUserId() === $user->id;
            } else if ($user->hasRole(config('larapress.ecommerce.product_owner_role_ids'))) {
                $tag = explode('-', $object->tags);
                if (count($tag) > 0) {
                    if ($tag[0] === 'course' || $tag[0] === 'azmoon') {
                        $access = in_array(intval($tag[1]), $user->getOwenedProductsIds());
                    }
                }
            }
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
            'tag_course' => config('larapress.ecommerce.routes.products.provider'),
        ]);
    }
}
