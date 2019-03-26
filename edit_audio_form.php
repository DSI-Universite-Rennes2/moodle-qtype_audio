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
 * Defines the editing form for the audio question type.
 *
 * @package   qtype_audio
 * @copyright 2019 Université Rennes 2 {@link https://www.univ-rennes2.fr}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Description editing form definition.
 *
 * @copyright 2019 Université Rennes 2
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_audio_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    protected function definition_inner($mform) {
        // We don't need this default element.
        $mform->removeElement('defaultmark');
        $mform->addElement('hidden', 'defaultmark', 0);
        $mform->setType('defaultmark', PARAM_RAW);

        // Audio file.
        $audiofileoptions = array('subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => array('mp3'));
        $mform->addElement('filemanager', 'audiofile', get_string('audiofile', 'qtype_audio'), $attributes = null, array('accepted_types' => 'mp3'));
        $mform->addRule('audiofile', null, 'required');
        $mform->addHelpButton('audiofile', 'audiofile', 'qtype_audio');

        // Limit playback.
        $mform->addElement('selectyesno', 'limitplayback', get_string('limitplayback', 'qtype_audio'));
        $mform->setDefault('limitplayback', 0);
        $mform->setType('limitplayback', PARAM_INT);
        $mform->addRule('limitplayback', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('limitplayback', 'limitplayback', 'qtype_audio');

        // Playback count.
        // Note: can't mix 'required' and 'disabledIf'. See https://moodle.org/mod/forum/discuss.php?d=357673.
        $mform->addElement('text', 'playbackcount', get_string('playbackcount', 'qtype_audio'));
        $mform->setDefault('playbackcount', 1);
        $mform->setType('playbackcount', PARAM_INT);
        $mform->disabledIf('playbackcount', 'limitplayback', 'eq', 0);
        $mform->addHelpButton('playbackcount', 'playbackcount', 'qtype_audio');

        // Controls.
        $mform->addElement('selectyesno', 'controls', get_string('showcontrols', 'qtype_audio'));
        $mform->setDefault('controls', 1);
        $mform->setType('controls', PARAM_INT);
        $mform->addRule('controls', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('controls', 'showcontrols', 'qtype_audio');
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        if (empty($question->id) === true) {
            return $question;
        }

        if (empty($question->options) === true) {
            return $question;
        }

        // Handle audio file.
        $question->audiofile = file_get_submitted_draft_itemid('audiofile');

        file_prepare_draft_area(
            $question->audiofile, // Draftid.
            $this->context->id,   // Context.
            'qtype_audio',        // Component.
            'audiofile',          // Filearea.
            (int) $question->id,  // Itemid.
            array('subdirs' => 0, 'maxfiles' => 1) // Options.
        );

        // Handle other options.
        $question->limitplayback = $question->options->limitplayback;
        $question->playbackcount = $question->options->playbackcount;
        $question->controls = $question->options->controls;

        return $question;
    }

    public function validation($fromform, $files) {
        $errors = parent::validation($fromform, $files);

        // Don't allow empty playback count if limit playback is enabled.
        if (empty($fromform['limitplayback']) === false) {
            if (empty($fromform['playbackcount']) === true) {
                $errors['playbackcount'] = get_string('playbackcountmustbegreaterthanzero', 'qtype_audio');
            }
        }

        return $errors;
    }

    public function qtype() {
        return 'audio';
    }
}
