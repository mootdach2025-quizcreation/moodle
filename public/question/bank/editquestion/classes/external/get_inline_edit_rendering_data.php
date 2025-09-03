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

use core\context;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use qtype_multichoice\output\inline_edit_view;
use question_bank;

/**
 * Get the data required to render the inline editing template for a question.
 *
 * @package    qbank_editquestion
 * @copyright  2025 Moodle Moot DACH Team 1
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_inline_edit_rendering_data extends external_api {
    
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'questionid' => new external_value(PARAM_INT, 'The id of the question to be displayed'),
        ]);
    }
    
    /**
     * Handles the status form submission.
     *
     * @param int $questionid The id of the question to be updated.
     * @return string JSON blob of the question template.
     */
    public static function execute(int $questionid): string {
        global $PAGE;

        [
            'questionid' => $questionid,
        ] = self::validate_parameters(self::execute_parameters(), [
            'questionid' => $questionid,
        ]);

        // Check the request is valid.
        $questiondata = question_bank::load_question_data($questionid);
        $context = context::instance_by_id($questiondata->contextid);
        self::validate_context($context);
        question_require_capability_on($questiondata, 'edit');

        if ($questiondata->qtype !== 'multichoice') {
            throw new \core\exception\coding_exception(
                'Currenltly this code only works with multiple choice question types.',
            );
        }

        return json_encode((new inline_edit_view($questiondata))->export_for_template(
            $PAGE->get_renderer('core')));
    }

    /**
     * Returns description of method result value.
     */
    public static function execute_returns(): external_value{
        return new external_value(PARAM_RAW, 'Template date to render the editing view of the question.', VALUE_REQUIRED);
    }
}
