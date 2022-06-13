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
 * Edit form class
 *
 * @package    block_customleaderboard
 * @copyright  2021 Brainstation23
 * @author     Brainstation23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_customleaderboard_edit_form extends block_edit_form {

    /**
     * Adds configuration fields in edit configuration for the block
     * @param StdClass $mform moodle form stdClass objects
     * @return void
     */
    protected function specific_definition ($mform) {
        global $DB, $PAGE;
        $PAGE->requires->js_call_amd('block_customleaderboard/configure_block', 'init', array());
        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        // A sample string variable with a default value.
        $mform->addElement('text', 'config_msg', get_string('blockstring', 'block_customleaderboard'));
        $mform->setDefault('config_msg', 'test msg');
        $mform->setType('config_msg', PARAM_RAW);

        $leaderboardtype = array();
        $leaderboardtype[""] = get_string('leaderboardtype:select', 'block_customleaderboard');
        $leaderboardtype["coursetotal"] = get_string('leaderboardtype:coursetotal', 'block_customleaderboard');
        $leaderboardtype["enrollment"] = get_string('leaderboardtype:enrollment', 'block_customleaderboard');
        $leaderboardtype["discussionpost"] = get_string('leaderboardtype:discussionpost', 'block_customleaderboard');
        $leaderboardtype["quizleaderboard"] = get_string('leaderboardtype:quizleaderboard', 'block_customleaderboard');

        $mform->addElement('select', 'config_leaderboardtype', get_string('leaderboardtype', 'block_customleaderboard'),
        $leaderboardtype, ['id' => 'id_config_leaderboardtype']);

        $coursesql = "SELECT * FROM {course} WHERE format != 'site'";
        $coursedata = $DB->get_records_sql($coursesql);

        $coursearray = array();
        $coursearray[0] = "All";
        foreach ($coursedata as $row) {
            $coursearray[$row->id] = $row->fullname;
        }

        $mform->addElement('html', '<div id="courseleaderboard_configs" style="display: none">');
        $mform->addElement('select', 'config_courseid', get_string('config:courseselect', 'block_customleaderboard'), $coursearray);
        $mform->addElement('html', '</div>');

        $quizorderbyoptions = array();
        $quizorderbyoptions["avg_grade"] = get_string('quizorderoption:grade', 'block_customleaderboard');
        $quizorderbyoptions["avg_finishtime"] = get_string('quizorderoption:finishtime', 'block_customleaderboard');

        $mform->addElement('html', '<div id="quizleaderboard_configs" style="display: none">');
        $mform->addElement('html', 'Columns:');

        $mform->addElement('advcheckbox', 'config_quizavggrade',
        get_string('quizorderoption:grade', 'block_customleaderboard'), null,
        ['id' => 'id_config_quizavggrade']);
        $mform->addElement('advcheckbox', 'config_quizavgtime',
        get_string('quizorderoption:finishtime', 'block_customleaderboard'), null,
        ['id' => 'id_config_quizavgtime']);

        $mform->addElement('html', '<div id="orderby_div" style="display: none">');
        $mform->addElement('select', 'config_quizorderby', get_string('quizorderby:label', 'block_customleaderboard'),
        $quizorderbyoptions);
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>');

        // User filter configs.
        $userfilteroptions = array();
        $userfilteroptions["none"] = get_string('userfilteroption:none', 'block_customleaderboard');
        $userfilteroptions["user_profile_field"] = get_string('userfilteroption:profilefield', 'block_customleaderboard');

        $mform->addElement('html', '<div id="userfilter_div" style="display: none">');
        $mform->addElement('select', 'config_userfilter',
        get_string('userfilter:label', 'block_customleaderboard'), $userfilteroptions,
        ['id' => 'id_config_userfilter']);
        $mform->addElement('html', '</div>');

        $fieldlistquery = "SELECT * FROM {user_info_field}";
        $fielddata = $DB->get_records_sql($fieldlistquery);
        $fieldarray = array();
        $fieldarray[0] = "None";
        foreach ($fielddata as $row) {
            $fieldarray[$row->id] = $row->shortname;
        }
        $mform->addElement('html', '<div id="userfield_div" style="display: none">');
        $mform->addElement('select', 'config_userfield',
        get_string('userfilter:userfield', 'block_customleaderboard'), $fieldarray);
        $mform->addElement('text', 'config_userfieldvalue',
        get_string('userfilter:fieldvalue', 'block_customleaderboard'));
        $mform->addElement('html', '</div>');
        $mform->setType('config_userfieldvalue', PARAM_RAW);
        // End of user filter configs.

        $datalimitoptions = array();
        $datalimitoptions[""] = get_string('leaderboardtype:select', 'block_customleaderboard');
        $datalimitoptions[5] = get_string('datalimitoption:top5', 'block_customleaderboard');
        $datalimitoptions[10] = get_string('datalimitoption:top10', 'block_customleaderboard');
        $datalimitoptions[20] = get_string('datalimitoption:top20', 'block_customleaderboard');
        $datalimitoptions[1] = get_string('datalimitoption:all', 'block_customleaderboard');

        $mform->addElement('select', 'config_datalimit',
        get_string('datalimitlabel', 'block_customleaderboard'), $datalimitoptions);

        $mform->addElement('header', 'config_styleheader', 'Custom CSS style variables');

        $mform->addElement('text', 'config_headerbg', 'Header background color:');
        $mform->setDefault('config_headerbg', '');
        $mform->setType('config_headerbg', PARAM_RAW);

        $mform->addElement('text', 'config_headertc', 'Header text color:');
        $mform->setDefault('config_headertc', '');
        $mform->setType('config_headertc', PARAM_RAW);

        $mform->addElement('text', 'config_oddrowbg', 'Odd table row background color:');
        $mform->setDefault('config_oddrowbg', '');
        $mform->setType('config_oddrowbg', PARAM_RAW);

        $mform->addElement('text', 'config_oddrowtc', 'Odd table row text color:');
        $mform->setDefault('config_oddrowtc', '');
        $mform->setType('config_oddrowtc', PARAM_RAW);

        $mform->addElement('text', 'config_evenrowbg', 'Even table row background color:');
        $mform->setDefault('config_evenrowbg', '');
        $mform->setType('config_evenrowbg', PARAM_RAW);

        $mform->addElement('text', 'config_evenrowtc', 'Even table row text color:');
        $mform->setDefault('config_evenrowtc', '');
        $mform->setType('config_evenrowtc', PARAM_RAW);

        $mform->addElement('text', 'config_blockbg', 'Block background color:');
        $mform->setDefault('config_blockbg', '');
        $mform->setType('config_blockbg', PARAM_RAW);

        $mform->addElement('text', 'config_tablebg', 'Table background color:');
        $mform->setDefault('config_tablebg', '');
        $mform->setType('config_tablebg', PARAM_RAW);

        $mform->addElement('text', 'config_tbordercolor', 'Table Outer Border Color:');
        $mform->setDefault('config_tbordercolor', '');
        $mform->setType('config_tbordercolor', PARAM_RAW);

        $mform->addElement('text', 'config_tbradius', 'Table Outer Border Radius:');
        $mform->setDefault('config_tbradius', '');
        $mform->setType('config_tbradius', PARAM_RAW);

        $mform->addElement('text', 'config_cellspacing', 'Table Cell Spacing:');
        $mform->setDefault('config_cellspacing', '');
        $mform->setType('config_cellspacing', PARAM_RAW);

    }
}
