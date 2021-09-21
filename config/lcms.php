<?php

return [
    // introduced product type names by LCMS package
    'product_typenames' => [
        'course' => 'course',
        'session' => 'session',
    ],

    'gifts' => [
        // gift amount for customer on registration
        'registeration_gift' => [
            'amount' => 0,
            'currency' => 1,
        ],

        // gift amount for user when completed profile
        'profle_gift' => [
            'amount' => 0,
            'currency' => 1,
        ],

        // gift amount when user introduced customer and he/she is registered
        'introducers_gift' => [
            'amount' => 0,
            'currency' => 1,
        ]
    ],


    // Form id for support user profile data
    'support_profile_form_id' => null,

    // Form id filled for student when uploading files in courses/sessions
    'course_file_upload_default_form_id' => null,
    // Form id filled for student when marking participant in courses/sessions
    'course_presense_default_form_id' => null,

    // Form id filled for student to become part of support group
    'support_group_default_form_id' => null,
    // Form id filled when student is registering with introducer id
    'introducer_default_form_id' => null,
    // Form id used when retrieving support user settings
    'support_settings_default_form_id' => null,

    'teacher_support_form_id' => null,

    // Support users role ids
    'support_role_ids' => [],
    // Roles used when randomazing students in support groups
    'support_randomizer_role_ids' => [],

    // Student user role ids
    'customer_role_ids' => [],
];
