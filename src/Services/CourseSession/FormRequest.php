<?php

namespace Larapress\LCMS\Services\CourseSession;

use Larapress\FileShare\Services\FileUpload\FileUploadRequest;

/**
 * Send files to a session.
 *
 * @bodyParam file file required File content.
 */
class FormRequest extends FileUploadRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // already handled in CRUD middleware
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required|file',
        ];
    }


    public function getAccess()
    {
        return 'private';
    }

    public $title = null;
    public function getTitle()
    {
        return $this->title;
    }
}
