<?php

namespace Larapress\LCMS\Services\CourseSession;

use Larapress\ECommerce\Models\Product;

trait FormEntryTagCourseTrait
{
    /**
     * Undocumented function
     *
     * @return FormEntryTagResolveRelationship
     */
    public function tag_course()
    {
        return new FormEntryTagCourseRelation($this, Product::query());
    }
}
