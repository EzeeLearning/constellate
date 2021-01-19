<?php

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_ezee_constellate/showactivity',
        get_string('showactivity', 'block_ezee_constellate'),
        get_string('showactivitydesc', 'block_ezee_constellate'),
        0));

    $settings->add(new admin_setting_configcheckbox('block_ezee_constellate/learningplan',
        get_string('learningplan', 'block_ezee_constellate'),
        get_string('learningplandesc', 'block_ezee_constellate'),
        0));
}