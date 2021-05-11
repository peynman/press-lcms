<?php

namespace Larapress\LCMS\Services\SupportGroup;

use Larapress\CRUD\Extend\Helpers;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Larapress\CRUD\Models\Role;
use Larapress\Profiles\Models\FormEntry;

trait BaseSupportGroupUserTrait {
    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_profile_support()
    {
        return $this->hasOne(
            FormEntry::class,
            'user_id'
        )->where('form_id', config('larapress.lcms.support_profile_form_id'));
    }

    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_support_registration_entry()
    {
        return $this->hasOne(
            FormEntry::class,
            'user_id'
        )->where('form_id', config('larapress.lcms.support_group_default_form_id'));
    }


    /**
     * Undocumented function
     *
     * @return null|int
     */
    public function getSupportUserId()
    {
        if (!is_null($this->form_support_registration_entry)) {
            $tags = $this->form_support_registration_entry->tags;
            if (Str::startsWith($tags, 'support-group-')) {
                return Str::substr($tags, Str::length('support-group-'));
            }
        }

        return null;
    }

    /**
     * Undocumented function
     *
     * @return Carbon|null
     */
    public function getSupportUserStartedDate()
    {
        if (!is_null($this->form_support_registration_entry)) {
            if (isset($this->form_support_registration_entry->data['values']['support_ids'])) {
                $suppIds = $this->form_support_registration_entry->data['values']['support_ids'];
                if (count($suppIds) > 0 && isset($suppIds[count($suppIds) - 1]['updated_at'])) {
                    return Carbon::parse($suppIds[count($suppIds) - 1]['updated_at']);
                }
            } else {
                return $this->form_support_registration_entry->created_at;
            }
        }

        return null;
    }


    /**
     * Undocumented function
     *
     * @return Role|null
     */
    public function getSupportUserRole()
    {
        $userRole = DB::table('user_role')->where('user_id', $this->getSupportUserId())->first();
        if (!is_null($userRole)) {
            return Role::find($userRole->role_id);
        }

        return null;
    }

    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_support_introducer_entry()
    {
        return $this->hasOne(
            FormEntry::class,
            'user_id'
        )->where('form_id', config('larapress.lcms.introducer_default_form_id'));
    }

    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_support_user_profile()
    {
        return new FormEntryUserSupportProfileRelationship($this);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getSupportUserProfileAttribute()
    {
        return $this->form_support_user_profile;
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    public function getIntroducerDataAttribute()
    {
        return Helpers::getCachedValue(
            'larapress.users.' . $this->id . '.introducer',
            function () {
                $entry = $this->form_entries()
                    ->where('form_id', config('larapress.lcms.introducer_default_form_id'))
                    ->first();
                if (!is_null($entry)) {
                    $introducer_id = explode('-', $entry->tags)[2];
                    $class = config('larapress.crud.user.class');
                    $introducer = call_user_func([$class, 'find'], $introducer_id);
                    return [$introducer, $entry];
                }
            },
            ['user.introducer:' . $this->id],
            null
        );
    }
}
