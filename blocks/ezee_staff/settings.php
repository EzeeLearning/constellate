<?php

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_ezee_staff/showcourses',
        get_string('showcourses', 'block_ezee_staff'),
        get_string('showcoursesdesc', 'block_ezee_staff'),
        0));
}