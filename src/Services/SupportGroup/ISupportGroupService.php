<?php

namespace Larapress\LCMS\Services\SupportGroup;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Larapress\Profiles\IProfileUser;
use Larapress\ECommerce\IECommerceUser;
use Larapress\LCMS\Services\SupportGroup\Requests\MySupportGroupUpdateRequest;
use Larapress\LCMS\Services\SupportGroup\Requests\SupportGroupUpdateRequest;

interface ISupportGroupService
{
    /**
     * Undocumented function
     *
     * @param SupportGroupUpdateRequest $request
     * @return Response
     */
    public function updateUsersSupportGroup(SupportGroupUpdateRequest $request);


    /**
     * Undocumented function
     *
     * @param Request $request
     * @param IProfileUser $user
     * @param IProfileUser|int $supportUser
     *
     * @return Response
     */
    public function updateUserSupportGroup(Request $request, IECommerceUser $user, $supportUser);


    /**
     * Undocumented function
     *
     * @param MySupportGroupUpdateRequest $request
     * @param int|IProfileUser $supportUser
     *
     * @return Response
     */
    public function updateMySupportGroup(MySupportGroupUpdateRequest $request, $supportUser);


    /**
     * Undocumented function
     *
     * @param IECommerceUser $user
     * @param IECommerceUser $supportUser
     *
     * @return void
     */
    public function giftUserForSupportGroupRegistration(IECommerceUser $user, IECommerceUser $supportUser);

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     *
     * @return FormEntry[]
     */
    public function getIntroducedUsersList($user);

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     *
     * @return void
     */
    public function resetSupportGroupCache($user);
}
