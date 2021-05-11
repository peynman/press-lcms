<?php

namespace Larapress\LCMS\Services\CourseSession;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Larapress\ECommerce\Services\Product\IProductService;
use Larapress\FileShare\Services\FileUpload\IFileUploadService;

/**
 * View content for Courses
 *
 * @group LCMS
 */
class ContentController extends Controller
{

    public static function registerWebRoutes()
    {
        Route::any('session/{session_id}/{file_type}/{file_id}/download', '\\' . self::class . '@downloadFile')
            ->name('session.any.file.download');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function downloadFile(IProductService $service, IFileUploadService $fileService, Request $request, $session_id, $file_type, $file_id)
    {
        return $service->checkProductLinkAccess(
            $request,
            $session_id,
            $file_id,
            function ($request, $product, $link) use ($fileService) {
                return $fileService->serveFile($request, $link, false);
            }
        );
    }
}
