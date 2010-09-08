<?php
/**
 * Exit functions
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 */

/**
 * exit_error() - Exit PHP with error
 *
 * @param		string	Error title
 * @param		string	Error text
 */
function exit_error($title,$text="", $toptab='') {
	global $HTML,$group_id;
	$HTML->header(array('title'=>_('Exiting with error'), 'group'=>$group_id, 'toptab'=>$toptab));
	echo '<h1>' . _('Exiting with error') . '</h1>';
	echo $HTML->error_msg($title.'<br />'.htmlspecialchars($text));
	$HTML->footer(array());
	exit;
}

/**
 * exit_permission_denied() - Exit with permission denied error
 *
 * @param		string	$reason_descr
 */
function exit_permission_denied($reason_descr='') {
	if(!session_loggedin()) {
		exit_not_logged_in();
	} else {
		if (!$reason_descr) {
			$reason_descr=_('This project\'s administrator will have to grant you permission to view this page.');
		}
		exit_error(_('Permission denied.'),$reason_descr);
	}
}

/**
 * exit_not_logged_in() - Exit with not logged in error
 */
function exit_not_logged_in() {
	//instead of a simple error page, now take them to the login page
	header ("Location: ".util_make_url ("/account/login.php?triggered=1&return_to=".urlencode(getStringFromServer('REQUEST_URI'))));
	exit;
}

/**
 * exit_no_group() - Exit with no group chosen error
 */
function exit_no_group() {
	exit_error(_('Error - No project was chosen, project does not exist or you can\'t access it.'));
}

/**
 * exit_missing_param() - Exit with missing required parameters error
 */
function exit_missing_param() {
	exit_error(_('Error - Missing required parameters.'));
}

/**
 * exit_disabled() - Exit with disabled feature error.
 */
function exit_disabled() {
	exit_error(_('Error - The Site Administrator has turned off this feature.'));
}

/**
 * exit_form_double_submit() - Exit with double submit error.
 */
function exit_form_double_submit() {
	exit_error(_('Error - You Attempted To Double-submit this item. Please avoid double-clicking.'));
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
