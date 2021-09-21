<?php

namespace Larapress\LCMS\Services\SupportGroup\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam user_ids int[] required_without:all_none_supp_users List of user ids to change support group. Example: [332, 244, 545]
 * @bodyParam support_user_id int required_without:random_support_id Supporting user id.
 * @bodyParam random_support_id boolean Distribute users between all availabel users with support role.
 * @bodyParam all_none_supp_users boolean Distribute all users without a support group.
 */
class SupportGroupUpdateRequest extends FormRequest
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
            'user_ids.*' => 'required|exists:users,id',
            'support_user_id' => 'required_without:random_support_id|exists:users,id',
            'random_support_id' => 'nullable',
            'all_none_supp_users' => 'nullable',
        ];
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getUserIds()
    {
        return $this->get('user_ids', []);
    }

    /**
     * Undocumented function
     *
     * @return int|null
     */
    public function getSupportUserID()
    {
        return $this->get('support_user_id', null);
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function shouldRandomizeSupportIds()
    {
        return $this->get('random_support_id', false);
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function shouldUseAllNoneSupportUsers()
    {
        return $this->get('all_none_supp_users', false);
    }
}
