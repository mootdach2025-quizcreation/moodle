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

use assignfeedback_ousendtoetma\external\get_cvps_using_service;
use core_external\external_api;

/**
 * Tests for get_inline_edit_rendering_data web service.
 *
 * @package    qbank_editquestion
 * @copyright  2025 Moodle Moot DACH Team 1
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \qbank_editquestion\external\get_inline_edit_rendering_data
 */
final class get_inline_edit_rendering_data_test extends \advanced_testcase {

    public function test_get_inline_edit_rendering_data_words(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a question bank.
        $course = $this->getDataGenerator()->create_course();
        $qbank = $this->getDataGenerator()->create_module('qbank', ['course' => $course->id]);
        $qbankcontext = \context_module::instance($qbank->cmid);

        // Create a question there.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $questiongenerator->create_question_category(['contextid' => $qbankcontext->id]);
        $question = $questiongenerator->create_question('multichoice', null, ['category' => $category->id]);

        // Call the web service function.
        $result = get_inline_edit_rendering_data::execute($question->id);

        // Verify the result matches what is specified. (This throws if not.)
        $cleanedresult = external_api::clean_returnvalue(
            get_inline_edit_rendering_data::execute_returns(),
            $result,
        );
        $this->assertEquals($result, $cleanedresult);

        // Ensure the result is valid.
        $this->assertJson($result);
        $data = json_decode($result);
        $this->assertEquals($question->questiontext, $data->questiontext);
        $this->assertEquals('One', $data->answers[0]->answer);
        $this->assertCount(4, $data->answers);
    }
}
