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

namespace qbank_editquestion;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/bank.php');

use core_user;
use question_bank;
use qbank_editquestion\external\update_question_answers;
use qbank_editquestion\external\update_question_fields;

class update_question_fields_test extends \advanced_testcase {

    /** @var \stdClass course record. */
    protected $course;
    
    /** @var mixed. */
    protected $user;
    
    /**
     * Called before every test.
     */
    public function setUp(): void {
        global $USER;
        parent::setUp();
        $this->setAdminUser();
        $this->course = $this->getDataGenerator()->create_course();
        $this->user = $USER;
    }
    
    /**
     * Test updating a question text
     */
    public function test_update_question_text() {
        global $DB;
        $this->resetAfterTest(true);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('multichoice', null, ['category' => $cat->id]);
        $result = update_question_fields::execute($question->id, [['partname' => 'question-name', 'value' => 'newname']]);
        $this->assertTrue($result);
        $questionname = $DB->get_field('question', 'name', ['id' => $question->id]);
        print_object($questionname);
        $this->assertEquals('newname', $questionname);
    }

}