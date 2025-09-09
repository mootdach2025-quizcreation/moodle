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
use core_external\external_value;
use question_bank;
use core\exception\invalid_parameter_exception;

/**
 * Create empty question of a certain type
 *
 * @package    qbank_editquestion
 * @copyright  2025 Moodle Moot DACH Team 1
 * @author     Thomas Wedekind <Thomas.Wedekind@univie.ac.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_empty_question extends external_api {
    const QUESTION_UNEDITED_STRING = '<!-- This question is not edited yet -->';
    
    /**
     * Returns description of method parameters.
     * 
     * @return external_function_parameters.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'qtypeplugin' => new external_value(PARAM_COMPONENT, 'The question type (e.g. qtype_multichoice)'),
            'questioncategoryid' => new external_value(PARAM_INT, 'The question category where it will be created'),
        ]);
    }
    
    /**
     * Executes the webservice call.
     *
     * @param string $qtypeplugin The question type (e.g. "qtype_multichoice")
     * @param int $questioncategoryid The question category id
     * @return int The created question id
     */
    public static function execute($qtypeplugin, $questioncategoryid) {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'qtypeplugin' => $qtypeplugin,
            'questioncategoryid' => $questioncategoryid,
        ]);

        // Get category & context.
        $category = $DB->get_record('question_categories', ['id' => $params['questioncategoryid']], '*', MUST_EXIST);
        $context  = \context::instance_by_id($category->contextid);
        self::validate_context($context);

        // Check capability.
        if (!has_capability('moodle/question:add', $context)) {
            throw new invalid_parameter_exception('You do not have permission to add questions in this category.');
        }

        // Check if qtype is working.
        $qtype = preg_replace('/^qtype_/', '', $params['qtypeplugin']);
        if (!question_bank::qtype_enabled($qtype)) {
            throw new \moodle_exception('cannotenable', 'question', '', $qtype);
        }

        // Create the new question object.
        $qtypeobj = question_bank::get_qtype($qtype);
        $question = new \stdClass();
        $question->qtype = $qtype;
        $question->createdby = $USER->id;
        $question->idnumber = null;
        $question->name = get_string('defaultquestionname','qbank_editquestion');
        $question->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_DRAFT;
        $question->category = $params['questioncategoryid'];
        $question->questiontext = [
            'text' => self::QUESTION_UNEDITED_STRING,
            'format' => FORMAT_HTML,
        ];
        $question->answer = [
            1=>
             [
                 'answertext' => self::QUESTION_UNEDITED_STRING,
                 'answerformat' => FORMAT_HTML,
                 'format' => FORMAT_HTML,
                 'text' => self::QUESTION_UNEDITED_STRING,
             ],
            2=>
             [
                 'answertext' => self::QUESTION_UNEDITED_STRING,
                 'answerformat' => FORMAT_HTML,
                 'format' => FORMAT_HTML,
                 'text' => self::QUESTION_UNEDITED_STRING,
                 
             ]
         ];
        $question->fraction = [1 => 1, 2 => 0];
        $question->correctfeedback = ['text' => '', 'format' => FORMAT_HTML];
        $question->single = true;
        $question->shuffleanswers = true;
        $question->answernumbering = 'none';
        $question->partiallycorrectfeedback = $question->correctfeedback;
        $question->incorrectfeedback = $question->correctfeedback;
        $question->feedback = [1 => [
            'format' => FORMAT_HTML,
            'text' => self::QUESTION_UNEDITED_STRING,
            ],
            2 => [
                'format' => FORMAT_HTML,
                'text' => self::QUESTION_UNEDITED_STRING,
            ]
            ];
        // Save the question.
        $qtypeobj->save_question($question, $question);
        
        // Fire event for creation.
        $event = \core\event\question_created::create_from_question_instance($question, $context);
        $event->trigger();
        
        return $question->id;
    }
    
    /**
     * Returns description of method result value.
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_INT, 'The id of the new question', VALUE_REQUIRED);
    }
}
