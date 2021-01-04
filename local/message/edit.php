<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/message/classes/form/edit.php');

global $DB;

$PAGE->set_url(new moodle_url('/local/message/edit.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Edit');


$mform = new edit();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    redirect($CFG->wwwroot . '/local/message/manage.php', 'Form cancelled');
    
} else if ($fromform = $mform->get_data()) {
  //In this case you process validated data. $mform->get_data() returns data posted in form.

  //Output form data to page
  //var_dump($fromform);
  //die;
  
  $recordtoinsert = new stdClass();
  $recordtoinsert->messagetext = $fromform->messagetext;
  $recordtoinsert->messagetype = $fromform->messagetype;

  $DB->insert_record('local_message', $recordtoinsert);

  redirect($CFG->wwwroot . '/local/message/manage.php', 'Message ' . $fromform->messagetext . ' created');
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();