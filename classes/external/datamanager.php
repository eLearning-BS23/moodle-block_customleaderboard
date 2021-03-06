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
 * This block simply outputs the data.
 *
 * @copyright  2021 Brainstation23
 * @author     Brainstation23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_customleaderboard\external;
defined('MOODLE_INTERNAL') || die();

/**
 * This block simply outputs the data.
 *
 * @copyright  2021 Brainstation23
 * @author     Brainstation23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class datamanager {

    /**
     * Return data for All course leaderboard
     * @param $data Array array of stdclass objects
     * @return Array of stdClass
     */
    public function make_all_course_leaderboard_data ($data) {
        $usermarksdictionary = array();
        $usernamedictionary = array();
        $userids = array();
        foreach ($data as $row) {
            if (isset($usermarksdictionary[$row->userid])) {
                $previousgrade = (float) $usermarksdictionary[$row->userid];
                $currentgrade = (float) $row->grade;
                $total = $previousgrade + $currentgrade;
                $usermarksdictionary[$row->userid] = $total;
            } else {
                $usermarksdictionary[$row->userid] = $row->grade;
                $usernamedictionary[$row->userid] = $row->display_name;
                array_push($userids, $row->userid);
            }
        }

        $userids = array_unique($userids);
        $responsedata = array();
        foreach ($userids as $id) {
            $temprow = new \stdClass();
            $temprow->userid = $id;
            $temprow->display_name = $usernamedictionary[$id];
            $temprow->grade = $usermarksdictionary[$id];
            array_push($responsedata, $temprow);
        }

        return $responsedata;
    }

    /**
     * Return data for All course leaderboard
     * @param int $datalimit Shows the data limit of query
     * @return Array of stdClass
     */
    public function get_enrollment_data ($datalimit) {
        global $DB;
        $limit = $this->return_data_limit($datalimit);
        $enrollmentsql = "SELECT c.id, c.fullname, COUNT(ue.id) AS enroled_count
                        FROM {course} c
                        JOIN {enrol} en ON en.courseid = c.id
                        JOIN {user_enrolments} ue ON ue.enrolid = en.id
                        GROUP BY c.id, c.fullname
                        ORDER BY enroled_count DESC ".$limit;
        $enrolldata = $DB->get_recordset_sql($enrollmentsql);

        $datalist = array();
        foreach ($enrolldata as $record) {
            array_push($datalist, $record);
        }
        $enrolldata->close();
        return $datalist;
    }

    /**
     * Return data for course leaderboard
     * @param int $courseid specific course id
     * @param int $datalimit Shows the data limit of query
     * @param Array $userfilterdata denotes user filter logic
     * @return Array of stdClass
     */
    public function get_course_leaderboard_data ($courseid, $datalimit, $userfilterdata) {
        global $DB;
        $data = array();
        $limit = $this->return_data_limit($datalimit);
        $queryparts = $this->get_user_filter_query_part($userfilterdata);
        if ($courseid == 0) {
            $sql = "SELECT "
                    ." ut.id AS userid,"
                    ." ut.firstname AS first, ut.lastname AS last, ut.firstname AS display_name,"
                    ." c.fullname AS course, c.id AS courseid, cc.name AS category,"
                    ." gg.finalgrade AS grade, "
                    ." gg.timemodified AS modified_time, "
                    ." CASE WHEN gi.itemtype =:coursestring2 THEN c.fullname ELSE gi.itemname END AS item_name"
                    ." FROM {course} c "
                    ." JOIN {context} ctx ON c.id = ctx.instanceid"
                    ." JOIN {role_assignments} ra ON ra.contextid = ctx.id"
                    ." JOIN {user} ut ON ut.id = ra.userid"
                    ." JOIN {grade_grades} gg ON gg.userid = ut.id"
                    ." JOIN {grade_items} gi ON gi.id = gg.itemid"
                    ." JOIN {course_categories} cc ON cc.id = c.category "
                .$queryparts["join"]
                ." WHERE  gi.courseid = c.id AND gi.itemtype = :coursestring1 AND ".$queryparts["whereclause"]
                ." ORDER BY grade DESC ".$limit;
            if (count($queryparts["params"]) > 0) {
                $params = $queryparts["params"];
                $params["coursestring1"] = 'course';
                $params["coursestring2"] = 'course';
                $data = $DB->get_recordset_sql($sql, $params);
            } else {
                $params = array();
                $params["coursestring1"] = 'course';
                $params["coursestring2"] = 'course';
                $data = $DB->get_recordset_sql($sql, $params);
            }
        } else {
            $sql = " SELECT"
                    ." ut.id AS userid,"
                    ." ut.firstname AS first ,"
                    ." ut.lastname AS last,"
                    ." ut.firstname AS display_name,"
                    ." c.fullname AS course,"
                    ." c.id AS courseid,"
                    ." cc.name AS category,"
                    ." CASE WHEN gi.itemtype = :coursestring2 THEN c.fullname ELSE gi.itemname END AS item_name,"
                    ." gg.finalgrade AS grade,"
                    ." gg.timemodified AS modified_time"
                    ." FROM {course} c"
                    ." JOIN {context} ctx ON c.id = ctx.instanceid"
                    ." JOIN {role_assignments} ra ON ra.contextid = ctx.id"
                    ." JOIN {user} ut ON ut.id = ra.userid"
                    ." JOIN {grade_grades} gg ON gg.userid = ut.id"
                    ." JOIN {grade_items} gi ON gi.id = gg.itemid"
                    ." JOIN {course_categories} cc ON cc.id = c.category "
                .$queryparts["join"]
                ." WHERE  gi.courseid = c.id AND gi.itemtype = :coursestring1 AND c.id=:courseid AND ".$queryparts["whereclause"]
                ." ORDER BY grade DESC ".$limit;

            $paramsarray = $queryparts["params"];
            $paramsarray["courseid"] = $courseid;
            $paramsarray["coursestring1"] = 'course';
            $paramsarray["coursestring2"] = 'course';

            $data = $DB->get_recordset_sql($sql, $paramsarray);
        }

        $datalist = array();
        foreach ($data as $record) {
            array_push($datalist, $record);
        }
        $data->close();
        return $datalist;
    }

    /**
     * Return query parts for userfilter
     * @param Array $userfilterdata denotes user filter logic
     * @return Array of string containing query parts
     */
    public function get_user_filter_query_part ($userfilterdata) {
        $userfilter = $userfilterdata['userfilter'];
        $userfield = $userfilterdata['userfield'];
        $userfieldvalue = $userfilterdata['userfieldvalue'];

        $queryparts = array();

        if ($userfilter == "user_profile_field") {
            $joinpart = " JOIN {user_info_data} uid ON ut.id = uid.userid AND uid.fieldid=:fieldid";
            $whereclause = " uid.data=:fieldvalue ";
            $queryparams = array();
            $queryparams["fieldid"] = $userfield;
            $queryparams["fieldvalue"] = $userfieldvalue;

            $queryparts["join"] = $joinpart;
            $queryparts["whereclause"] = $whereclause;
            $queryparts["params"] = $queryparams;

            return $queryparts;
        } else {
            $queryparts["join"] = "";
            $queryparts["whereclause"] = " true ";
            $queryparts["params"] = array();
            return $queryparts;
        }
    }

    /**
     * Return data for discussion post leaderboard
     * @param int $datalimit Query row limit
     * @param Array $userfilterdata denotes user filter logic
     * @return Array of stdClass
     */
    public function get_discussion_post_data ($datalimit, $userfilterdata) {
        global $DB;
        $limit = $this->return_data_limit($datalimit);
        $queryparts = $this->get_user_filter_query_part($userfilterdata);

        $sql = " SELECT "
                ." pt.userid,"
                ." ut.firstname,"
                ." ut.lastname,"
                ." ut.firstname AS displayname,"
                ." COUNT(pt.id) postcount"
                ." FROM {forum_posts} pt"
                ." JOIN {user} ut ON ut.id = pt.userid"
                .$queryparts["join"]
                ." WHERE ".$queryparts["whereclause"]
                ." GROUP BY pt.userid,ut.firstname,ut.lastname,displayname "
                ." ORDER BY postcount DESC ".$limit;

        if (count($queryparts["params"]) > 0) {
            $data = $DB->get_recordset_sql($sql, $queryparts["params"]);
        } else {
            $data = $DB->get_recordset_sql($sql);
        }

        $response = array();
        foreach ($data as $row) {
            array_push($response, $row);
        }
        return $response;
    }

    /**
     * Return data for discussion post leaderboard
     * @param int $datalimit Query row limit
     * @param int $quizgradecolumn denotes if grade column should be included
     * @param int $quiztimecolumn denotes if time column should be included
     * @param String $orderby set query order by
     * @param Array $userfilterdata denotes user filter logic
     * @return Array of stdClass
     */
    public function get_quiz_data ($datalimit, $quizgradecolumn, $quiztimecolumn, $orderby, $userfilterdata) {
        global $DB;
        $limit = $this->return_data_limit($datalimit);
        $queryparts = $this->get_user_filter_query_part($userfilterdata);
        if ($quizgradecolumn == 1 && $quiztimecolumn == 0) {
            $sql = " SELECT "
                    ." mqg.userid, "
                    ." ut.firstname, "
                    ." ut.lastname, "
                    ." ut.firstname AS displayname, "
                    ." AVG((mqg.grade)) avg_grade "
                    ." FROM {quiz_grades} mqg "
                    ." JOIN {user} ut ON mqg.userid = ut.id "
                    .$queryparts["join"]
                    ." WHERE ".$queryparts["whereclause"]
                    ." GROUP BY mqg.userid,ut.firstname,ut.lastname,displayname "
                    ." ORDER BY avg_grade DESC ".$limit;
        } else if ($quizgradecolumn == 0 && $quiztimecolumn == 1) {
            $sql = " SELECT "
                    ." mqa.userid, "
                    ." ut.firstname, "
                    ." ut.lastname, "
                    ." ut.firstname AS displayname, "
                    ." AVG((mqa.timefinish-mqa.timestart)) avg_timediff "
                    ." FROM {quiz_attempts} mqa "
                    ." JOIN {user} ut ON mqa.userid = ut.id "
                    .$queryparts["join"]
                    ." WHERE ".$queryparts["whereclause"]
                    ." GROUP BY mqg.userid,ut.firstname,ut.lastname,displayname "
                    ." ORDER BY avg_timediff ASC ".$limit;
        } else if ($quizgradecolumn == 1 && $quiztimecolumn == 1) {
            if ($orderby == "avg_grade") {
                $orderbysql = " ORDER BY avg_grade DESC ";
            } else if ($orderby == "avg_finishtime") {
                $orderbysql = " ORDER BY avg_timediff ASC ";
            } else {
                $orderbysql = " ORDER BY avg_grade DESC ";
            }
            $sql = " SELECT "
                    ." mqg.userid, "
                    ." ut.firstname, "
                    ." ut.lastname, "
                    ." ut.firstname AS displayname, "
                    ." AVG((mqg.grade)) avg_grade, "
                    ." AVG((mqa.timefinish-mqa.timestart)) avg_timediff "
                    ." FROM {quiz_grades} mqg "
                    ." JOIN {quiz_attempts} mqa ON mqg.userid = mqa.userid "
                    ." JOIN {user} ut ON mqg.userid = ut.id "
                    .$queryparts["join"]
                    ." WHERE ".$queryparts["whereclause"]
                    ." GROUP BY mqg.userid,ut.firstname,ut.lastname,displayname ".$orderbysql." ".$limit;
        } else {
            return array();
        }

        if (count($queryparts["params"]) > 0) {
            $data = $DB->get_recordset_sql($sql, $queryparts["params"]);
        } else {
            $data = $DB->get_recordset_sql($sql);
        }

        $response = array();
        foreach ($data as $row) {
            array_push($response, $row);
        }
        return $response;
    }

    /**
     * Return data limit part of query
     * @param int $datalimit Query row limit
     * @return String
     */
    public function return_data_limit ($datalimit) {
        $limit = (int) $datalimit;
        switch ($limit) {
            case 1:
                return "";
            case 5:
                return " LIMIT 5 ";
            case 10:
                return " LIMIT 10 ";
            case 20:
                return " LIMIT 20 ";
            default:
                return " LIMIT 10 ";
        }
    }
}
