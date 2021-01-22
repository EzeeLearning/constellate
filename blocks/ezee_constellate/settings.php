<?php

if ($ADMIN->fulltree) {

    //Choose whether to show staff list table or staff activity graph on the dashboard by default
    $settings->add(new admin_setting_configcheckbox('block_ezee_constellate/showactivity',
        get_string('showactivity', 'block_ezee_constellate'),
        get_string('showactivitydesc', 'block_ezee_constellate'),
        0));

    //Display staff completion and statistics based on learning plans rather than assigned courses
    $settings->add(new admin_setting_configcheckbox('block_ezee_constellate/learningplan',
        get_string('learningplan', 'block_ezee_constellate'),
        get_string('learningplandesc', 'block_ezee_constellate'),
        0));
}