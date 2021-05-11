<?php

namespace Larapress\LCMS\Services\CourseSession;

interface ICourseSessionRepository
{
    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @return Product[]
     */
    public function getTodayCourseSessions($user);

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @return Product[]
     */
    public function getWeekCourseSessions($user);

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @return FormEntry[]
     */
    public function getIntroducedUsersList($user);
}
