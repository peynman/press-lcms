<?php

namespace Larapress\LCMS\Services\SupportGroup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * LCMS Support Group
 *
 * @group LCMS
 */
class SupportGroupController extends Controller
{
    public static function registerRoutes()
    {
        Route::post('support-group/update', '\\' . self::class . '@updateSupportGroups')
            ->name('users.edit.support-group');

        Route::post('support-group/my/update', '\\' . self::class . '@updateMySupportGroup')
            ->name('users.any.support-group');
    }

    /**
     * Admin update users SupportGroup
     *
     * @param Request $request
     *
     * @return Response
     */
    public function updateSupportGroups(ISupportGroupService $service, SupportGroupUpdateRequest $request)
    {
        return $service->updateUsersSupportGroup($request);
    }


    /**
     * Update my SupportGroup
     *
     * @param Request $request
     *
     * @return Response
     */
    public function updateMySupportGroup(ISupportGroupService $service, MySupportGroupUpdateRequest $request)
    {
        return $service->updateMySupportGroup($request, $request->getSupportUserID());
    }
}
