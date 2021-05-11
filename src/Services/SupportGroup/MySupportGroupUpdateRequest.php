<?php

namespace Larapress\LCMS\Services\SupportGroup;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam support_user_id int required Target user id with support role.
 */
class MySupportGroupUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'support_user_id' => 'required|exists:users,id',
        ];
    }

    /**
     * Undocumented function
     *
     * @return null|int
     */
    public function getSupportUserID()
    {
        return $this->get('support_user_id', null);
    }
}
