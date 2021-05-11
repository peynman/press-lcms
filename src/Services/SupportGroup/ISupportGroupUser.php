<?php

namespace Larapress\LCMS\Services\SupportGroup;
use Illuminate\Support\Carbon;
use Larapress\CRUD\Models\Role;

interface ISupportGroupUser {
    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_profile_support();

    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_support_registration_entry();

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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_support_introducer_entry();

    /**
     * Undocumented function
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form_support_user_profile();

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getSupportUserProfileAttribute();

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getIntroducerDataAttribute();
}
