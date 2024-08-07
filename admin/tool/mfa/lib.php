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
 * Moodle MFA plugin lib
 *
 * @package     tool_mfa
 * @author      Mikhail Golenkov <golenkovm@gmail.com>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Main hook.
 *
 * e.g. Add permissions logic across a site or course
 *
 * @param mixed $courseorid
 * @param mixed $autologinguest
 * @param mixed $cm
 * @param mixed $setwantsurltome
 * @param mixed $preventredirect
 * @return void
 * @throws \moodle_exception
 */
function tool_mfa_after_require_login($courseorid = null, $autologinguest = null, $cm = null,
    $setwantsurltome = null, $preventredirect = null) {

    global $SESSION;
    // Tests for hooks being fired to test patches.
    if (PHPUNIT_TEST) {
        $SESSION->mfa_login_hook_test = true;
    }

    if (empty($SESSION->tool_mfa_authenticated)) {
        \tool_mfa\manager::require_auth($courseorid, $autologinguest, $cm, $setwantsurltome, $preventredirect);
    }
}

/**
 * Extends navigation bar and injects MFA Preferences menu to user preferences.
 *
 * @param navigation_node $navigation
 * @param stdClass $user
 * @param context_user $usercontext
 * @param stdClass $course
 * @param context_course $coursecontext
 *
 * @return void or null
 * @throws \moodle_exception
 */
function tool_mfa_extend_navigation_user_settings($navigation, $user, $usercontext, $course, $coursecontext) {
    global $PAGE;

    // Only inject if user is on the preferences page.
    $onpreferencepage = $PAGE->url->compare(new moodle_url('/user/preferences.php'), URL_MATCH_BASE);
    if (!$onpreferencepage) {
        return null;
    }

    if (\tool_mfa\manager::is_ready() && \tool_mfa\manager::possible_factor_setup()) {
        $url = new moodle_url('/admin/tool/mfa/user_preferences.php');
        $node = navigation_node::create(get_string('preferences:header', 'tool_mfa'), $url,
            navigation_node::TYPE_SETTING);
        $usernode = $navigation->find('useraccount', navigation_node::TYPE_CONTAINER);
        $usernode->add_node($node);
    }
}

/**
 * Triggered as soon as practical on every moodle bootstrap after config has
 * been loaded. The $USER object is available at this point too.
 */
function tool_mfa_after_config() {
    global $CFG, $SESSION;

    // Tests for hooks being fired to test patches.
    // Store in $CFG, $SESSION not present at this point.
    if (PHPUNIT_TEST) {
        $CFG->mfa_config_hook_test = true;
    }

    // Check for not logged in.
    if (isloggedin() && !isguestuser()) {
        // If not authenticated, force login required.
        if (empty($SESSION->tool_mfa_authenticated)) {
            \tool_mfa\manager::require_auth();
        }
    }
}

/**
 * Any plugin typically an admin tool can add new bulk user actions
 */
function tool_mfa_bulk_user_actions() {
    return [
        'tool_mfa_reset_factors' => new action_link(
            new moodle_url('/admin/tool/mfa/reset_factor.php'),
            get_string('resetfactor', 'tool_mfa')
        ),
    ];
}

/**
 * Serves any files for the guidance page.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function tool_mfa_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    // Hardcode to only send guidance files from the top level.
    $fs = get_file_storage();
    $file = $fs->get_file(
        $context->id,
        'tool_mfa',
        'guidance',
        0,
        '/',
        $args[1]
    );
    if (!$file) {
        send_file_not_found();
        return false;
    }
    send_file($file, $file->get_filename());

    return true;
}
