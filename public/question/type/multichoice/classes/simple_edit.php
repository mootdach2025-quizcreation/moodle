<?php

namespace qtype_multichoice;

class simple_edit {
    const NOT_SET_TEXT = '<!-- This value has not been set yet. -->';
    /**
     * Given the name of an editable part of the question, get what need to be updated in the DB.
     *
     * @param int $questionid The question id.
     * @param string $partname Name of the part of the question to change.
     * @return array with three elements:
     *      - DB table name.
     *      - Name of the column to update.
     *      - Selector to identify which row needs to be changed.
     */
    public static function resolved_question_part_name(
        \stdClass $question,
        string $partname
        ): array {
            if ($partname === 'question-name') {
                return [
                    'question',
                    'name',
                    ['id' => $question->id],
                ];
            } else if ($partname === 'question-text') {
                return [
                    'question',
                    'questiontext',
                    ['id' => $question->id],
                ];
            } else if (preg_match('~^choice-(\d+)$~', $partname, $matches)) {
                return [
                    'question_answers',
                    'answer',
                    ['id' => $matches[1], 'question' => $question->id],
                ];
            } else {
                throw new coding_exception('Unrecognised question part ' . $partname);
            }
    }
}
