<?php

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_ezee_report/showcourses',
        get_string('showcourses', 'block_ezee_report'),
        get_string('showcoursesdesc', 'block_ezee_report'),
        0));
}