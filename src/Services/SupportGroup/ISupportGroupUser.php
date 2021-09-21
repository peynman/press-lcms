<?php

namespace Larapress\LCMS\Services\SupportGroup;
use Illuminate\Support\Carbon;
use Larapress\CRUD\Models\Role;
use Larapress\Profiles\IProfileUser;

interface ISupportGroupUser {
    /**
     * Entry for support-profile
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_profile_support();

    /**
     * Entry for support-group-{id}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_support_registration_entry();

    /**
     * Entry for introducer-{id}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_support_introducer_entry();

    /**
     * Support user entry for his/her profile
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_support_user_profile();

    /**
     * Undocumented function
     *
     * @return null|FormEntry
     */
    public function getProfileAttribute();

    /**
     * Undocumented function
     *
     * @return null|int
     */
    public function getSupportUserId();

    /**
     * Undocumented function
     *
     * @return Carbon|null
     */
    public function getSupportUserStartedDate();


    /**
     * Undocumented function
     *
     * @return Role|null
     */
    public function getSupportUserRole();

    /**
     * Undocumented function
     *
     * @return array|null
     */
    public function getSupportUserProfileAttribute();

    /**
     * Undocumented function
     *
     * @return null|int
     */
    public function getIntroducerId();


    /**
     * Undocumented function
     *
     * @return IProfileUser|null
     */
    public function getIntroducer();
}
