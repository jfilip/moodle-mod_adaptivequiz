<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Adaptive quiz view attempt report script
 *
 * @package    mod_adaptivequiz
 * @copyright  2013 onwards Remote-Learner {@link http://www.remote-learner.ca/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');

$id = required_param('cmid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

if (!$cm = get_coursemodule_from_id('adaptivequiz', $id)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error("coursemisconf");
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

require_capability('mod/adaptivequiz:viewreport', $context);

$adaptivequiz  = $DB->get_record('adaptivequiz', array('id' => $cm->instance), '*');
$PAGE->set_url('/mod/adaptivequiz/viewattemptreport.php', array('cmid' => $cm->id));
$PAGE->set_title(format_string($adaptivequiz->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$records = $DB->get_records('adaptivequiz_attempt', array('instance' => $adaptivequiz->id, 'userid' => $userid), 'timemodified DESC');

// Check if recordset contains records
if (empty($records)) {
    $url = new moodle_url('/mod/adaptivequiz/viewreport', array('cmid' => $cm->id));
    print_error('noattemptrecords', 'adaptivequiz', $url);
}

$output = $PAGE->get_renderer('mod_adaptivequiz');

/* print header information */
$header = $output->print_reporting_page_header();
/* Output attempts table */
$user = $DB->get_record('user', array('id' => $userid));
$reporttable = $output->print_attempt_report_table($records, $cm, $user);
/* OUtput return to main reports page button */
$backbtn = $output->print_back_to_main_report_page($cm->id);
/* Output footer information */
$footer = $output->print_reporting_page_footer();

echo $header;
echo $reporttable;
echo $backbtn;
echo $footer;