<?php

function local_message_before_footer() {

    global $DB, $USER;

    $sql = "SELECT lm.id, lm.messagetext, lm.messagetype
            FROM {local_message} lm
            WHERE lm.id NOT IN (SELECT messageid FROM {local_message_read} WHERE userid = :userid)";
    $params =[
        'userid' => $USER->id
    ];
    $messages = $DB->get_records_sql($sql, $params);

    foreach ($messages as $message) {
        $type;
        switch ($message->messagetype) {
            case 0:
                $type = \core\output\notification::NOTIFY_INFO;
                break;
            case 1:
                $type = \core\output\notification::NOTIFY_WARNING;
                break;
            case 2:
                $type = \core\output\notification::NOTIFY_ERROR;
                break;
            case 3:
                $type = \core\output\notification::NOTIFY_SUCCESS;
                break;
        }

        \core\notification::add($message->messagetext, $type);

        $readrecord = new stdClass();
        $readrecord->messageid = $message->id;
        $readrecord->userid = $USER->id;
        $readrecord->timeread = time();
        //$DB->insert_record('local_message_read', $readrecord);
    }

}