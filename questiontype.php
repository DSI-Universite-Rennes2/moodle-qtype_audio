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
 * Question type class for the audio 'question' type.
 *
 * @package   qtype_audio
 * @copyright 2019 Université Rennes 2 {@link https://www.univ-rennes2.fr}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');

/**
 * The audio 'question' type.
 *
 * @copyright 2019 Université Rennes 2
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_audio extends question_type {
    /**
     * Determine if this a real question type or not.
     *
     * For example the description question type is not really a question type.
     *
     * @return bool True for real question type, false for fake question type.
     */
    public function is_real_question_type() {
        return false;
    }

    /**
     * Determine if this question type can be used by the random question type.
     *
     * @return bool True if it can be randomised, else False.
     */
    public function is_usable_by_random() {
        return false;
    }

    /**
     * Whether this question type can perform a frequency analysis of student
     * responses.
     *
     * If this method returns true, you must implement the get_possible_responses
     * method, and the question_definition class must implement the
     * classify_response method.
     *
     * @return bool whether this report can analyse all the student responses
     * for things like the quiz statistics report.
     */
    public function can_analyse_responses() {
        return false;
    }

    /**
     * Loads the question type specific options for the question. Used by data_preprocessing() for edit form.
     *
     * This function loads any question type specific options for the
     * question from the database into the question object. This information
     * is placed in the $question->options field. A question type is
     * free, however, to decide on a internal structure of the options field.
     *
     * @param object $question The question object for the question. This object should be updated to include the question type specific information (it is passed by reference).
     *
     * @return bool Indicates success or failure.
     */
    public function get_question_options($question) {
        global $DB;

        parent::get_question_options($question);

        // Get additional information from database
        // and attach it to the question object.
        $question->options = $DB->get_record('qtype_audio', array('questionid' => $question->id));

        return true;
    }

    /**
     * Saves (creates or updates) a question.
     *
     * Given some question info and some data about the answers
     * this function parses, organises and saves the question
     * It is used by {@link question.php} when saving new data from
     * a form, and also by {@link import.php} when importing questions
     * This function in turn calls {@link save_question_options}
     * to save question-type specific data.
     *
     * Whether we are saving a new question or updating an existing one can be
     * determined by testing !empty($question->id). If it is not empty, we are updating.
     *
     * The question will be saved in category $form->category.
     *
     * @param object $question the question object which should be updated. For a new question will be mostly empty.
     * @param object $form the object containing the information to save, as if from the question editing form.
     * @param object $course not really used any more.
     *
     * @return object On success, return the new question object. On failure,
     *       return an object as follows. If the error object has an errors field,
     *       display that as an error message. Otherwise, the editing form will be
     *       redisplayed with validation errors, from validation_errors field, which
     *       is itself an object, shown next to the form fields. (I don't think this
     *       is accurate any more.)
     */
    public function save_question($question, $form) {
        global $DB;

        // Make very sure that audios can't be created with a grade of
        // anything other than 0.
        $form->defaultmark = 0;

        $question = parent::save_question($question, $form);

        // Handle audio file.
        list($question->category) = explode(',', $form->category);
        $context = $this->get_context_by_category_id($question->category);

        file_save_draft_area_files($form->audiofile, $context->id, 'qtype_audio', 'audiofile', (int) $question->id, array('subdirs' => 0, 'maxfiles' => 1));

        return $question;
    }

    /**
     * Saves question-type specific options
     *
     * This is called by {@link save_question()} to save the question-type specific data
     * @return object $result->error or $result->notice
     * @param object $question  This holds the information from the editing form,
     *      it is not a standard question object.
     */
    public function save_question_options($question) {
        global $DB;

        // The code is used for calculated, calculatedsimple and calculatedmulti qtypes.
        $context = $question->context;

        // Calculated options.
        $update = true;
        $options = $DB->get_record('qtype_audio', array('questionid' => $question->id));
        if ($options === false) {
            $update = false;
            $options = new stdClass();
            $options->questionid = $question->id;
        }

        // $options->audiofile = $question->audiofile;
        $options->limitplayback = $question->limitplayback;
        $options->playbackcount = $question->playbackcount;
        $options->controls = $question->controls;

        if ($update) {
            $DB->update_record('qtype_audio', $options);
        } else {
            $DB->insert_record('qtype_audio', $options);
        }
    }

    /**
     * Returns the number of question numbers which are used by the question
     *
     * This function returns the number of question numbers to be assigned
     * to the question. Most question types will have length one; they will be
     * assigned one number. The 'description' type, however does not use up a
     * number and so has a length of zero. Other question types may wish to
     * handle a bundle of questions and hence return a number greater than one.
     *
     * @param object $question The question whose length is to be determined. Question type specific information is included.
     *
     * @return int The number of question numbers which should be assigned to the question.
     */
    public function actual_number_of_questions($question) {
        // Used for the feature number-of-questions-per-page
        // to determine the actual number of questions wrapped by this question.
        // The question type audio is not even a question
        // in itself so it will return ZERO!
        return 0;
    }

    /**
     * @param object $question
     *
     * @return number|null either a fraction estimating what the student would score by guessing, or null, if it is not possible to estimate.
     */
    public function get_random_guess_score($questiondata) {
        return null;
    }

    /**
     * Initialise question instance.
     *
     * @param question_definition $question
     * @param object $questiondata
     *
     * @return void
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);

        // Setup question options for renderer.
        foreach ((array) $questiondata->options as $key => $value) {
            if (in_array($key, array('id', 'questionid'), $strict = true) === true) {
                continue;
            }

            $question->{$key} = $value;
        }
    }

    /**
     * Deletes the question-type specific data when a question is deleted.
     *
     * @param int $question the question being deleted.
     * @param int $contextid the context this quesiotn belongs to.
     *
     * @return void
     */
    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('qtype_audio', array('questionid' => $questionid));

        parent::delete_question($questionid, $contextid);
    }

    /**
     * If your question type has a table that extends the question table, and
     * you want the base class to automatically save, backup and restore the extra fields,
     * override this method to return an array wherer the first element is the table name,
     * and the subsequent entries are the column names (apart from id and questionid).
     *
     * @return mixed array as above, or null to tell the base class to do nothing.
     */
    public function extra_question_fields() {
        return array('qtype_audio', 'limitplayback', 'playbackcount', 'controls');
    }

    /*
     * Export question to the Moodle XML format
     *
     * Export question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     */
    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $output = parent::export_to_xml($question, $format, $extra);

        // Handle audio file.
        $fs = get_file_storage();
        $contextid = $question->contextid;

        $files = $fs->get_area_files($contextid, 'qtype_audio', 'audiofile', $question->id);
        foreach ($files as $file) {
            $output .= '    ';
            $output .= '<file name="' . $file->get_filename() . '" encoding="base64">';
            $output .= base64_encode($file->get_content());
            $output .= "</file>\n";
        }

        return $output;
    }
}
