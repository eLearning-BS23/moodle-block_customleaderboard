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
 * Configure block js
 *
 * @module     block_customleaderboard
 * @copyright  2021 Brainstation23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    return {
        init: function() {
            // console.log('configure block js loaded test');
            // show courseleaderboard inputs if the leaderboard type is course leaderboard
            function manageUserField(){
                var userfilter = $( "#id_config_userfilter :selected" ).val();
                if(userfilter == "user_profile_field"){
                    document.getElementById('userfield_div').style.display = 'block';
                }
                else{
                    document.getElementById('userfield_div').style.display = 'none';
                }
            }


            function manageOrderBy(){
                var gradechecked = document.getElementById('id_config_quizavggrade').checked;
                var timechecked = document.getElementById('id_config_quizavgtime').checked;

                if(gradechecked && timechecked){
                    // console.log("both checked");
                    document.getElementById('orderby_div').style.display = 'block';
                }
                else{
                    // console.log("not both checked");
                    document.getElementById('orderby_div').style.display = 'none';
                }
            }

            function manageLeaderBoardType(){
                var leaderboardtype = $( "#id_config_leaderboardtype :selected" ).val();
                switch (leaderboardtype) {
                    case "coursetotal":
                        console.log("courseleader board");
                        document.getElementById('courseleaderboard_configs').style.display = 'block';
                        document.getElementById('quizleaderboard_configs').style.display = 'none';
                        document.getElementById('orderby_div').style.display = 'none';
                        document.getElementById('userfilter_div').style.display = 'block';
                        manageUserField();
                        break;
                    case "enrollment":
                        console.log("quizleader board");
                        document.getElementById('courseleaderboard_configs').style.display = 'none';
                        document.getElementById('quizleaderboard_configs').style.display = 'none';
                        document.getElementById('orderby_div').style.display = 'none';
                        document.getElementById('userfilter_div').style.display = 'none';
                        document.getElementById('userfield_div').style.display = 'none';
                        break;
                    case "discussionpost":
                        console.log("discussion board");
                        document.getElementById('courseleaderboard_configs').style.display = 'none';
                        document.getElementById('quizleaderboard_configs').style.display = 'none';
                        document.getElementById('orderby_div').style.display = 'none';
                        document.getElementById('userfilter_div').style.display = 'block';
                        manageUserField();
                        break;
                    case "quizleaderboard":
                        console.log("quizleader board");
                        document.getElementById('courseleaderboard_configs').style.display = 'none';
                        document.getElementById('quizleaderboard_configs').style.display = 'block';
                        manageOrderBy();
                        document.getElementById('userfilter_div').style.display = 'block';
                        manageUserField();
                        break;
                    default:
                        console.log("invalid leaderboard type");
                        break;
                }
            }

            manageLeaderBoardType();

            $('#id_config_leaderboardtype').on('change', function() {
                manageLeaderBoardType();
            });

            $('#id_config_quizavggrade').change(function () {
                manageOrderBy();
            });

            $('#id_config_quizavgtime').change(function () {
                manageOrderBy();
            });

            $('#id_config_userfilter').on('change', function() {
                manageUserField();
            });
        }
    };
});
