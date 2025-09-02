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

namespace qbank_editquestion\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/engine/bank.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use qbank_editquestion\editquestion_helper;
use question_bank;
use core\exception\invalid_parameter_exception;
use core_external\external_multiple_structure;

/**
 * create empty question of a certaint type
 *
 * @package    qbank_editquestion
 * @copyright  2025 Moodle Moot DACH Team 1
 * @author     Thomas Wedekind <Thomas.Wedekind@univie.ac.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_question_fields extends external_api {
    
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'questionid' => new external_value(PARAM_INT, 'The id of the question to be updated'),
            'updatedfields' => new external_multiple_structure([
                new external_single_structure([
                    'fieldname' => new external_value(PARAM_ALPHANUM, 'The name of the edited field'),
                    'value' => new external_value(PARAM_RAW, 'The value of the edited field'),
                ])
            ]),
        ]);
    }
    
    /**
     * Handles the status form submission.
     *
     * @param $questionid The id of the question to be updated.
     * @param $ The questioncategory id.
     * @return int the created question id
     */
    public static function execute($questionid, $questioncategoryid) {
        global $DB, $USER;

        $category = $DB->get_record('question', ['id' => $questionid], '*', MUST_EXIST);
        $context  = \context::instance_by_id($category->contextid);
        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), [
            'qtypeplugin' => $qtypeplugin,
            'context' => $context,
        ]);
        $qtype = preg_replace('/^qtype_/', '', $qtypeplugin);

        $qtypeobj = question_bank::get_qtype($qtype);
        $question = new \stdClass();
        $question->qtype = $qtype;
        $question->createdby = $USER->id;
        $question->idnumber = null;
        $question->status = \question_version_status::QUESTION_STATUS_DRAFT;
        $question->category = $questioncategoryid;
        $qtypeobj->save_question($question, $question);

        $event = \core\event\question_updated::create_from_question_instance($question, $context);
        $event->trigger();

        return $question;
    }
    
    /**
     * {@inheritDoc}
     * @see \core_external\external_api::validate_parameters()
     */
    public static function validate_parameters(\core_external\external_description $description, $params)
    {
        if (!has_capability('moodle/question:add', $params['context'])) {
            throw new invalid_parameter_exception();
        }
        self::validate_context($params['context']);
        if($params['qtypeplugin'] != 'qtype_multichoice') {
            throw new invalid_parameter_exception();
        }
        $qtype = preg_replace('/^qtype_/', '', $params['qtypeplugin']);
        if (!question_bank::qtype_enabled($qtype)) {
            throw new \moodle_exception('cannotenable', 'question', $qtype);
        }
    }
    
    
    /**
     * Returns description of method result value.
     */
    public static function execute_returns(): external_value{
        return new external_value(PARAM_INT, 'The id of the new question', VALUE_REQUIRED);
    }
}
