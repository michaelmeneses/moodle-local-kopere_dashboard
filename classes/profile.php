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
 * @created    15/05/17 03:13
 * @package    local_kopere_dashboard
 * @copyright  2017 Eduardo Kraus {@link http://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_dashboard;

defined('MOODLE_INTERNAL') || die();

use local_kopere_dashboard\util\dashboard_util;
use local_kopere_dashboard\util\header;
use local_kopere_dashboard\util\mensagem;

/**
 * Class profile
 * @package local_kopere_dashboard
 */
class profile {
    /**
     *
     */
    public function details() {
        global $DB, $PAGE, $CFG;

        $userid = optional_param('userid', 0, PARAM_INT);
        if ($userid == 0) {
            header::notfound(get_string_kopere('profile_invalid'));
        }

        $user = $DB->get_record('user', array('id' => $userid));

        header::notfound_null($user, get_string_kopere('profile_notfound'));

        dashboard_util::start_page(array(
            array('users::dashboard', get_string_kopere('profile_title')),
            fullname($user)
        ));

        echo '<div class="element-box">';

        $userpicture = new \user_picture($user);
        $userpicture->size = 110;
        $profileimageurl = $userpicture->get_url($PAGE)->out(false);

        $courses = enrol_get_all_users_courses($user->id);

        $coursehtml = '';
        if (!count($courses)) {
            $coursehtml = mensagem::warning(get_string_kopere('profile_notenrol'));
        }
        foreach ($courses as $course) {
            $sql
                = "SELECT ue.*
                         FROM {user_enrolments} ue
                         JOIN {enrol} e ON ( e.id = ue.enrolid AND e.courseid = :courseid )
                        WHERE ue.userid = :userid";
            $params = array('userid' => $user->id,
                'courseid' => $course->id
            );

            $enrolment = $DB->get_record_sql($sql, $params);

            if ($enrolment->timeend == 0) {
                $expirationend = get_string_kopere('profile_enrol_notexpires');
            } else {
                $expirationend = '<br>' . get_string_kopere('profile_enrol_expires') . ' <em>' .
                    userdate($enrolment->timeend, get_string_kopere('dateformat')) . '</em>';
            }

            $roleassignments = $DB->get_records('role_assignments',
                array('contextid' => $course->ctxid,
                    'userid' => $user->id
                ), '', 'DISTINCT roleid');

            $rolehtml = '';
            foreach ($roleassignments as $roleassignment) {
                $role = $DB->get_record('role', array('id' => $roleassignment->roleid));
                $rolehtml .= '<span class="btn btn-default">' . role_get_name($role) . '</span>';
            }

            $matriculastatus = '<span class="btn-danger">' . get_string_kopere('profile_enrol_inactive') . '</span>';
            if ($enrolment->status == 0) {
                $matriculastatus = '<span class="btn-success">' . get_string_kopere('profile_enrol_active') . '</span>';
            }
            $coursehtml
                .= '<li>
                    <h4 class="title">' . $course->fullname . '
                        <span class="status">' . $matriculastatus . '</span>
                    </h4>
                    <div>' . get_string_kopere('profile_enrol_start') . ' <em>' .
                userdate($enrolment->timestart, get_string_kopere('dateformat')) . '</em> ' . $expirationend . ' -
                        <button class="btn btn-info btn-xs" data-toggle="modal" data-target="#modal-edit"
                                data-href="open-ajax-table.php?userenrolment::mathedit&courseid=' . $course->id .
                '&ueid=' . $enrolment->id . '">' . get_string_kopere('profile_edit') . '</button>
                    </div>
                    <div class="roles">' . get_string_kopere('profile_enrol_profile') . ': ' . $rolehtml . '</div>
                </li>';
        }

        echo '<div class="profile-content">
                  <div class="table">
                      <div class="profile">
                          <img src="' . $profileimageurl . '" alt="' . fullname($user) . '">
                          <span class="name">' . $user->firstname . '
                                <span class="last">' . $user->lastname . '</span>
                                <span class="city">' . $user->city . '</span>
                          </span>
                          <div class="desc">' . $user->description . '</div>

                          <h2>' . get_string_kopere('profile_courses_title') . '</h2>
                          <ul class="personalDev">
                              ' . $coursehtml . '
                          </ul>

                      </div>
                       <div class="info">
                          <h3>' . get_string_kopere('profile_access_title') . '</h3>
                          <p>' . get_string_kopere('profile_access_first') . '<br/> <strong>' .
            userdate($user->firstaccess, get_string_kopere('dateformat')) . '</strong></p>
                          <p>' . get_string_kopere('profile_access_last') . '<br/>   <strong>' .
            userdate($user->lastaccess, get_string_kopere('dateformat')) . '</strong></p>
                          <p>' . get_string_kopere('profile_access_lastlogin') . '<br/>    <strong>' .
            userdate($user->lastlogin, get_string_kopere('dateformat')) . '</strong></p>
                          <h3>' . get_string_kopere('profile_userdate_title') . '</h3>
                          <p><a href="mailto:' . $user->email . '">' . $user->email . '</a></p>
                          <p>' . $user->phone1 . '</p>
                          <p>' . $user->phone2 . '</p>
                          <h3>' . get_string_kopere('profile_link_title') . '</h3>
                          <p><a target="_blank" href="' . $CFG->wwwroot . '/user/profile.php?id=' . $user->id . '">' .
            get_string_kopere('profile_link_profile') . '</a></p>
                          <p><a target="_blank" href="' . $CFG->wwwroot . '/user/editadvanced.php?id=' . $user->id . '">' .
            get_string_kopere('profile_link_edit') . '</a></p>
                          <p><a target="_blank" href="' . $CFG->wwwroot . '/course/loginas.php?id=1&user=' . $user->id .
            '&sesskey=' . sesskey() . '">' . get_string_kopere('profile_access') . '</a></p>
                      </div>';
        echo '    </div>
              </div>';

        echo '</div>';
        dashboard_util::end_page();
    }
}