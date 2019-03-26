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
 * Description 'question' renderer class.
 *
 * @package   qtype_audio
 * @copyright 2019 Université Rennes 2 {@link https://www.univ-rennes2.fr}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for audio 'question's.
 *
 * @copyright  2019 Université Rennes 2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_audio_renderer extends qtype_renderer {
    /**
     * Generate the display of the formulation part of the question. This is the
     * area that contains the quetsion text, and the controls for students to
     * input their answers. Some question types also embed bits of feedback, for
     * example ticks and crosses, in this area.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     *
     * @return string HTML fragment.
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $DB;

        $question = $qa->get_question();

        $content = array();

        // Question text.
        $content[] = html_writer::tag('div', $question->format_questiontext($qa), array('class' => 'qtext'));

        // Audio tag.
        $attributes = array();
        $attributes['src'] = self::get_url_for_audio($qa);
        if (empty($question->controls) === false) {
            $attributes['controls'] = 1;
        } else {
            $attributes['autoplay'] = 1;
        }
        $content[] = html_writer::tag('audio', '', $attributes);

        // Options
        if (empty($question->limitplayback) === true) {
            $a = get_string('unlimited', 'qtype_audio');
        } else {
            $a = $question->playbackcount;
        }
        $content[] = html_writer::tag('div', get_string('playbackcounttimes', 'qtype_audio', $a), array('class' => 'qtext'));

        // Append all content in a single div.
        return html_writer::tag('div', implode(PHP_EOL, $content));
    }

    /**
     * In the question output there are some class="accesshide" headers to help
     * screen-readers. This method returns the text to use for the heading above
     * the formulation_and_controls section.
     *
     * @return string to use as the heading.
     */
    public function formulation_heading() {
        return get_string('informationtext', 'qtype_audio');
    }

    /**
     * Returns the URL for an image
     *
     * @param object $qa Question attempt object
     * @param string $filearea File area descriptor
     * @param int $itemid Item id to get
     * @return string Output url, or null if not found
     */
    protected static function get_url_for_audio(question_attempt $qa) {
        $question = $qa->get_question();
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $componentname = $question->qtype->plugin_name();
        $filearea = 'audiofile';
        $itemid = $question->id;

        $fs = get_file_storage();
        $draftfiles = $fs->get_area_files($question->contextid, $componentname, $filearea, $itemid, 'id');
        if ($draftfiles) {
            foreach ($draftfiles as $file) {
                if ($file->is_directory()) {
                    continue;
                }

                $url = moodle_url::make_pluginfile_url($question->contextid, $componentname, $filearea, "$qubaid/$slot/{$itemid}", '/', $file->get_filename());
                return $url->out();
            }
        }

        return null;
    }
}
