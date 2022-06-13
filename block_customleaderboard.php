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
 * Content Box block
 *
 * @package    block_customleaderboard
 * @copyright  2021 Brainstation23
 * @author     Brainstation23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/lib.php');
/**
 * This block simply outputs the Benefits Spots.
 *
 * @copyright  2021 Brainstation23
 * @author     Brainstation23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_customleaderboard extends block_base {

    /**
     * Initialize.
     *
     */
    public function init() {
        $this->title = get_string('leaderboard', 'block_customleaderboard');
    }

    /**
     * Return contents of block_customleaderboard block
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass();
        $this->content->text = $this->make_custom_content();
        return $this->content;
    }

    /**
     * Make custom content for block_customleaderboard block
     *
     * @return String
     */
    public function make_custom_content() {
        if (isset($this->config->leaderboardtype)) {
            if ($this->config->leaderboardtype == 'enrollment') {
                return $this->make_enrollment_table();
            } else if ($this->config->leaderboardtype == 'coursetotal') {
                return $this->make_course_leaderboard_table();
            } else if ($this->config->leaderboardtype == 'discussionpost') {
                return $this->make_discussion_post_table();
            } else if ($this->config->leaderboardtype == 'quizleaderboard') {
                return $this->make_quiz_table();
            } else {
                return get_string('leaderboardtypewarning', 'block_customleaderboard');
            }
        } else {
            return get_string('leaderboardtypewarning', 'block_customleaderboard');
        }
    }

    /**
     * Make HTML table for quiz leaderboard
     *
     * @return String
     */
    public function make_quiz_table() {
        $datalimit = $this->config->datalimit;

        $quizgradecolumn = $this->config->quizavggrade;
        $quiztimecolumn = $this->config->quizavgtime;
        $orderby = $this->config->quizorderby;

        // User filter.
        $userfilter = $this->config->userfilter;
        $userfield = $this->config->userfield;
        $userfieldvalue = $this->config->userfieldvalue;

        $userfilterdata = array();
        $userfilterdata['userfilter'] = $userfilter;
        $userfilterdata['userfield'] = $userfield;
        $userfilterdata['userfieldvalue'] = $userfieldvalue;

        $data = block_customleaderboard_get_quiz_data($datalimit, $quizgradecolumn, $quiztimecolumn, $orderby, $userfilterdata);
        
        $usernamelabel = get_string('tblcourseleader:username', 'block_customleaderboard');
        $avgtimelabel = get_string('quiztable:avgtime', 'block_customleaderboard');
        $avggradelabel = get_string('quiztable:avggrade', 'block_customleaderboard');

        $responsehtml = "";
        if ($quizgradecolumn == 1 && $quiztimecolumn == 0) {
            $headings = array($usernamelabel, $avggradelabel);
            $align = array('left');
            $table = new \html_table();
            $table->head = $headings;
            $table->align = $align;

            foreach ($data as $row) {
                $avggrade = $this->round_two_decimal($row->avg_grade);
                $table->data[] = array ($row->displayname, $avggrade);
            }
            $rowcount = count($data);
            $this->add_custom_css($table, $rowcount);
            $responsehtml = html_writer::table($table);
        } else if ($quizgradecolumn == 0 && $quiztimecolumn == 1) {
            $headings = array($usernamelabel, $avgtimelabel);
            $align = array('left');
            $table = new \html_table();
            $table->head = $headings;
            $table->align = $align;

            foreach ($data as $row) {
                $timediff = $this->round_two_decimal($row->avg_timediff);
                $table->data[] = array ($row->displayname, $timediff);
            }
            $rowcount = count($data);
            $this->add_custom_css($table, $rowcount);
            $responsehtml = html_writer::table($table);
        } else if ($quizgradecolumn == 1 && $quiztimecolumn == 1) {
            $headings = array($usernamelabel, $avgtimelabel, $avggradelabel);
            $align = array('left');
            $table = new \html_table();
            $table->head = $headings;
            $table->align = $align;

            foreach ($data as $row) {
                $avggrade = $this->round_two_decimal($row->avg_grade);
                $timediff = $this->round_two_decimal($row->avg_timediff);
                $table->data[] = array ($row->displayname, $timediff, $avggrade);
            }
            $rowcount = count($data);
            $this->add_custom_css($table, $rowcount);
            $responsehtml = html_writer::table($table);
        } else {
            $responsehtml = get_string('quiz:invalidcolumnlist', 'block_customleaderboard');
        }
        return $responsehtml;
    }

    /**
     * Make HTML table for discussion post leaderboard
     *
     * @return String
     */
    public function make_discussion_post_table() {
        $datalimit = $this->config->datalimit;
        // User filter.
        $userfilter = $this->config->userfilter;
        $userfield = $this->config->userfield;
        $userfieldvalue = $this->config->userfieldvalue;

        $userfilterdata = array();
        $userfilterdata['userfilter'] = $userfilter;
        $userfilterdata['userfield'] = $userfield;
        $userfilterdata['userfieldvalue'] = $userfieldvalue;

        $data = block_customleaderboard_get_discussion_post_data($datalimit, $userfilterdata);

        $userlabel = get_string('tblcourseleader:username', 'block_customleaderboard');
        $postlabel = get_string('tbldiscussion:postcount', 'block_customleaderboard');

        $headings = array($userlabel, $postlabel);
        $align = array('left');

        $table = new \html_table();
        $table->head = $headings;
        $table->align = $align;

        foreach ($data as $row) {
            $table->data[] = array ($row->displayname, $row->postcount);
        }
        $rowcount = count($data);
        $this->add_custom_css($table, $rowcount);
        return html_writer::table($table);
    }

    /**
     * Make HTML table for course leaderboard
     *
     * @return String
     */
    public function make_course_leaderboard_table() {
        $datalimit = $this->config->datalimit;
        $courseid = $this->config->courseid;
        $coursedata = array();
        if (isset($courseid)) {
            if (is_numeric($courseid)) {
                // User filter.
                $userfilter = $this->config->userfilter;
                $userfield = $this->config->userfield;
                $userfieldvalue = $this->config->userfieldvalue;

                $userfilterdata = array();
                $userfilterdata['userfilter'] = $userfilter;
                $userfilterdata['userfield'] = $userfield;
                $userfilterdata['userfieldvalue'] = $userfieldvalue;
                $coursedata = block_customleaderboard_get_course_leaderboard_data($courseid, $datalimit, $userfilterdata);
            
            } else {
                return get_string('leaderboardtypewarning', 'block_customleaderboard');
            }
        } else {
            return get_string('leaderboardtypewarning', 'block_customleaderboard');
        }

        if ($courseid == 0) {
            // Make HTML table.
            $userlabel = get_string('tblcourseleader:username', 'block_customleaderboard');
            $gradelabel = get_string('tblcourseleader:grade', 'block_customleaderboard');

            $headings = array($userlabel, $gradelabel);
            $align = array('left');

            $table = new \html_table();
            $table->head = $headings;
            $table->align = $align;

            $cleandata = block_customleaderboard_make_all_course_leaderboard_data($coursedata);
            
            foreach ($cleandata as $row) {
                $grade = $this->round_two_decimal($row->grade);
                $table->data[] = array ($row->display_name, $grade);
            }
            $rowcount = count($cleandata);
            $this->add_custom_css($table, $rowcount);
            return html_writer::table($table);
        } else {
            // Make HTML table.
            $userlabel = get_string('tblcourseleader:username', 'block_customleaderboard');
            $gradelabel = get_string('tblcourseleader:grade', 'block_customleaderboard');

            $headings = array($userlabel, $gradelabel);
            $align = array('left');

            $table = new \html_table();
            $table->head = $headings;
            $table->align = $align;

            $coursename = "";

            foreach ($coursedata as $row) {
                $coursename = $row->course;
                $grade = $this->round_two_decimal($row->grade);
                $table->data[] = array ($row->display_name, $grade);
            }
            $courselabel = get_string('tblenrollment:coursename', 'block_customleaderboard');
            $html = "<p>$courselabel: $coursename</p>";
            $html = $html.html_writer::table($table);

            $rowcount = count($coursedata);
            $this->add_custom_css($table, $rowcount);
            return $html;
        }
    }

    /**
     * Make HTML table for enrollment leaderboard
     *
     * @return String
     */
    public function make_enrollment_table() {
        $datalimit = $this->config->datalimit;
        $enrolldata = block_customleaderboard_get_enrollment_data($datalimit);
        
        $coursenamelabel = get_string('tblenrollment:coursename', 'block_customleaderboard');
        $enrolleduserlabel = get_string('tblenrollment:enrollment', 'block_customleaderboard');
        $headings = array($coursenamelabel, $enrolleduserlabel);
        $align = array('left');

        $table = new \html_table();
        $table->head = $headings;
        $table->align = $align;
        $rowcount = count($enrolldata);
        $this->add_custom_css($table, $rowcount);
        foreach ($enrolldata as $row) {
            $table->data[] = array ($row->fullname, $row->enroled_count);
        }
        return html_writer::table($table);
    }

    /**
     * Update title
     *
     * @return void
     */
    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = $this->config->msg;
            } else {
                $this->title = $this->config->msg;
            }

            if (empty($this->config->msg)) {
                $this->config->msg = get_string('defaulttext', 'block_customleaderboard');
            }
        }
    }

    /**
     * Allow the block to have a multiple instance
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Converts value to two decimal places
     *
     * @return boolean
     */
    public function round_two_decimal($number) {
        return number_format((float)$number, 2, '.', '');
    }

    /**
     * Adds custom css to table
     * @param stdClass $table HTML table class
     * @return boolean
     */
    public function add_custom_css($table, $rowcount) {
        // Set block background color.
        $blockbg = $this->config->blockbg;
        $blockcss = "";
        if ($blockbg != "") {
            $blockattributes = $this->html_attributes();
            $blockid = $blockattributes["id"];
            $blockcss = "#$blockid { background-color: $blockbg !important; }";
        }

        // Set table background color.
        $tablebgcss = "";
        $tablebg = $this->config->tablebg;
        if ($tablebg != "") {
            $tablebgcss = "background-color: $tablebg !important;";
        }

        // Set table outer border color.
        $bordercolorcss = "";
        $bordercolor = $this->config->tbordercolor;
        if ($bordercolor != "") {
            $bordercolorcss = "border: 2px solid $bordercolor;";
        }

        // Set outer border radius.
        $borderradiuscss = "";
        $borderradius = $this->config->tbradius;
        if ($borderradius != "") {
            $borderradiuscss = "border-radius: $borderradius;";
        }

        // Set cell spacing.
        $cellspacingcss = "";
        $cellspacing = $this->config->cellspacing;

        if ($cellspacing != "") {
            $cellspacingcss = "border-spacing: $cellspacing !important; border-collapse: separate !important;";
        }

        $attributes = array();
        $attributes["style"] = "$bordercolorcss $borderradiuscss $tablebgcss $cellspacingcss";

        $table->attributes = $attributes;

        $id = "customid-".uniqid();
        $table->id = $id;
        $evenrowcssclass = "even-$id";
        $oddrowcssclass = "odd-$id";

        $oddrowcss = "";
        $evenrowcss = "";
        $headercss = "";
        $tablecss = "";
        if (($this->config->oddrowbg !== "") && ($this->config->oddrowtc !== "")) {
            $oddrowbg = $this->config->oddrowbg;
            $oddrowtc = $this->config->oddrowtc;
            $oddrowcss = ".$oddrowcssclass{
                            background-color: $oddrowbg !important;
                            color: $oddrowtc !important;
                        }";
        }
        if (($this->config->evenrowbg !== "") && ($this->config->evenrowtc !== "")) {
            $evenrowbg = $this->config->evenrowbg;
            $evenrowtc = $this->config->evenrowtc;
            $evenrowcss = ".$evenrowcssclass{
                            background-color: $evenrowbg !important;
                            color: $evenrowtc !important;
                        }";
        }

        if (($this->config->headerbg !== "") && ($this->config->headertc !== "")) {
            $headerbg = $this->config->headerbg;
            $headertc = $this->config->headertc;
            $headercss = "#$id > thead > tr > th {
                            background-color: $headerbg !important;
                            color: $headertc !important;
                            }";
        }

        $css = "<style>
                    $blockcss
                    $headercss
                    $oddrowcss
                    $evenrowcss
                </style>";
        echo $css;
        for ($i = 0; $i < $rowcount; $i++) {
            $class = "";
            if ($i % 2 == 0) {
                $class = $evenrowcssclass;
            } else {
                $class = $oddrowcssclass;
            }
            $table->rowclasses[$i] = $class;
        }
    }
}
