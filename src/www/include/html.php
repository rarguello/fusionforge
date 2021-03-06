<?php
/**
 * Misc HTML functions
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) FusionForge Team
 * Copyright (C) 2010-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011-2014, Franck Villaume - TrivialDev
 * Copyright © 2011, 2012
 *	Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'include/minijson.php';

/**
 * html_generic_fileheader() - Output <html><head> and <meta/> inside.
 *
 * @param	$title	string
 *			Mandatory content of <title> attribute, will be HTML-secured
 * @throws	Exception
 */
function html_generic_fileheader($title) {
	global $HTML;

	if (!$title) {
		throw new Exception('A title is mandatory in XHTML!');
	}

	$HTML->headerHTMLDeclaration();
	echo "<head>\n";
	echo '<meta http-equiv="Content-Type" ' .
	    'content="text/html; charset=utf-8" />' . "\n";
	echo '<script type="text/javascript">//<![CDATA[' .
	    "\n\tvar sys_url_base = " . minijson_encode(util_make_url("/"),
	    false) . ";\n" .
	    "//]]></script>\n";
	$HTML->headerForgepluckerMeta();
	echo html_e('title', array(), util_html_secure($title));
}

/**
 * html_feedback_top() - Show the feedback output at the top of the page.
 *
 * @param	string	$feedback	The feedback.
 */
function html_feedback_top($feedback) {
	global $HTML;
	echo $HTML->feedback($feedback);
}

/**
 * html_warning_top() - Show the warning output at the top of the page.
 *
 * @param	string	$msg	The warning message.
 */
function html_warning_top($msg) {
	global $HTML;
	echo $HTML->warning_msg($msg);
}

/**
 * html_error_top() - Show the error output at the top of the page.
 *
 * @param	string	$msg	The error message.
 */
function html_error_top($msg) {
	global $HTML;
	echo $HTML->error_msg($msg);
}

/**
 * make_user_link() - Make a username reference into a link to that users User page on SF.
 *
 * @param	string	$username	The username of the user to link.
 * @param	string	$displayname	The name to display.
 * @return	string
 */
function make_user_link($username, $displayname = '') {
	if (empty($displayname))
		$displayname = $username;

	if (!strcasecmp($username, 'Nobody') || !strcasecmp($username, 'None')) {
		return $username;
	} else {
		return util_make_link('/users/'.$username, $displayname);
	}
}

/**
 * html_feedback_bottom() - Show the feedback output at the bottom of the page.
 *
 * @param	string	$feedback	The feedback.
 */
function html_feedback_bottom($feedback) {
	global $HTML;
	echo $HTML->feedback($feedback);
}

/**
 * html_blankimage() - Show the blank spacer image.
 *
 * @param	int	$height	The height of the image
 * @param	int	$width	The width of the image
 * @return	string
 */
function html_blankimage($height, $width) {
	return html_abs_image('/images/blank.png', $width, $height);
}

/**
 * html_abs_image() - Show an image given an absolute URL.
 *
 * @param	string	$url	URL
 * @param	int	$width	width of the image
 * @param	int	$height	height of the image
 * @param	array	$args	Any <img> tag parameters (i.e. 'border', 'alt', etc...)
 * @return	string
 */
function html_abs_image($url, $width, $height, $args) {
	global $use_tooltips;
	$args['src'] = $url;
	if (!$use_tooltips && isset($args['title'])) {
		$args['title'] = '';
	}
	if (!isset($args['alt'])) {
		$args['alt'] = '';
	}

	// Add image dimensions (if given)
	$width ? $args['width'] = $width : '';
	$height ? $args['height'] = $height : '';

	return html_e('img', $args);
}

/**
 * html_image() - Build an image tag of an image contained in $src
 *
 * @param	string	$src		The source location of the image
 * @param	int	$width		The width of the image
 * @param	int	$height		The height of the image
 * @param	array	$args		Any IMG tag parameters associated with this image (i.e. 'border', 'alt', etc...)
 * @param	bool	$display	DEPRECATED
 * @return	string
 */
function html_image($src, $width = 0, $height = 0, $args = array(), $display = true) {
	global $HTML;

	if (method_exists($HTML, 'html_image')) {
		$HTML->html_image($src, $width, $height, $args);
	}
	$s = ((session_issecure()) ? forge_get_config('images_secure_url') : forge_get_config('images_url') );
	return html_abs_image($s.$HTML->imgroot.$src, $width, $height, $args);
}

/**
 * html_get_language_popup() - Pop up box of supported languages.
 *
 * @param	string	$title		The title of the popup box.
 * @param	string	$selected	Which element of the box is to be selected.
 * @return	string	The html select box.
 */
function html_get_language_popup($title = 'language_id', $selected = 'xzxz') {
	$res = db_query_params('SELECT * FROM supported_languages ORDER BY name ASC',
		array());
	return html_build_select_box($res, $title, $selected, false);
}

/**
 * html_get_theme_popup() - Pop up box of supported themes.
 *
 * @param	string	$title		The title of the popup box.
 * @param	string	$selected	Which element of the box is to be selected.
 * @return	string	The html select box.
 */
function html_get_theme_popup($title = 'theme_id', $selected = 'xzxz') {
	$res = db_query_params('SELECT theme_id, fullname FROM themes WHERE enabled=true',
		array());
	$nbTheme = db_numrows($res);
	if ($nbTheme == 1) {
		$thetheme = db_result($res, 0, 'fullname');
		return util_html_secure($thetheme) . html_e('input', array(
			'type' => 'hidden',
			'name' => $title,
			'value' => db_result($res, 0, 'theme_id'),
			));
	} elseif ($nbTheme < 1) {
		return ("");
	} else {
		return html_build_select_box($res, $title, $selected, false);
	}
}

/**
 * html_get_ccode_popup() - Pop up box of supported country_codes.
 *
 * @param	string	$title		The title of the popup box.
 * @param	string	$selected	Which element of the box is to be selected.
 * @return	string	The html select box.
 */
function html_get_ccode_popup($title = 'ccode', $selected = 'xzxz') {
	$res = db_query_params('SELECT ccode,country_name FROM country_code ORDER BY country_name',
		array());
	return html_build_select_box($res, $title, $selected, false);
}

/**
 * html_get_timezone_popup() - Pop up box of supported Timezones.
 * Assumes you have included Timezones array file.
 *
 * @param	string	$title		The title of the popup box.
 * @param	string	$selected	Which element of the box is to be selected.
 * @return	string	The html select box.
 */
function html_get_timezone_popup($title = 'timezone', $selected = 'xzxz') {
	global $TZs;
	if ($selected == 'xzxzxzx') {
		$r = file('/etc/timezone');
		$selected = str_replace("\n", '', $r[0]);
	}
	return html_build_select_box_from_arrays($TZs, $TZs, $title, $selected, false);
}

/**
 * html_build_select_box_from_assoc() - Takes one assoc array and returns a pop-up box.
 *
 * @param	array	$arr		An array of items to use.
 * @param	string	$select_name	The name you want assigned to this form element.
 * @param	string	$checked_val	The value of the item that should be checked.
 * @param	bool	$swap		Whether we should swap the keys / names.
 * @param	bool	$show_100	Whether or not to show the '100 row'.
 * @param	string	$text_100	What to call the '100 row' defaults to none.
 * @return	string
 */
function html_build_select_box_from_assoc($arr, $select_name, $checked_val = 'xzxz', $swap = false, $show_100 = false, $text_100 = 'None') {
	if ($swap) {
		$keys = array_values($arr);
		$vals = array_keys($arr);
	} else {
		$vals = array_values($arr);
		$keys = array_keys($arr);
	}
	return html_build_select_box_from_arrays($keys, $vals, $select_name, $checked_val, $show_100, $text_100);
}

/**
 * html_build_select_box_from_array() - Takes one array, with the first array being the "id"
 * or value and the array being the text you want displayed.
 *
 * @param	array	$vals		An array of items to use.
 * @param	string	$select_name	The name you want assigned to this form element.
 * @param	string	$checked_val	The value of the item that should be checked.
 * @param	int	$samevals
 * @return	string
 */
function html_build_select_box_from_array($vals, $select_name, $checked_val = 'xzxz', $samevals = 0) {
	$return = '
		<select name="'.$select_name.'">';

	$rows = count($vals);

	for ($i = 0; $i < $rows; $i++) {
		if ($samevals) {
			$return .= "\n\t\t<option value=\"".$vals[$i]."\"";
			if ($vals[$i] == $checked_val) {
				$return .= ' selected="selected"';
			}
		} else {
			$return .= "\n\t\t<option value=\"".$i.'"';
			if ($i == $checked_val) {
				$return .= ' selected="selected"';
			}
		}
		$return .= '>'.htmlspecialchars($vals[$i]).'</option>';
	}
	$return .= '
		</select>';

	return $return;
}

/**
 * html_build_radio_buttons_from_arrays() - Takes two arrays, with the first array being the "id" or value and the other
 * array being the text you want displayed.
 *
 * The infamous '100 row' has to do with the SQL Table joins done throughout all this code.
 * There must be a related row in users, categories, et	, and by default that
 * row is 100, so almost every pop-up box has 100 as the default
 * Most tables in the database should therefore have a row with an id of 100 in it so that joins are successful
 *
 * @param	array	$vals		The ID or value
 * @param	array	$texts		Text to be displayed
 * @param	string	$select_name	Name to assign to this form element
 * @param	string	$checked_val	The item that should be checked
 * @param	bool	$show_100	Whether or not to show the '100 row'
 * @param	string	$text_100	What to call the '100 row' defaults to none
 * @param	bool	$show_any	Whether or not to show the 'Any row'
 * @param	string	$text_any	What to call the 'Any row' defaults to any
 * @return	string
 */
function html_build_radio_buttons_from_arrays($vals, $texts, $select_name, $checked_val = 'xzxz',
											  $show_100 = true, $text_100 = 'none', $show_any = false, $text_any = 'any') {
	if ($text_100 == 'none') {
		$text_100 = _('None');
	}
	$return = '';

	$rows = count($vals);
	if (count($texts) != $rows) {
		$return .= 'Error: uneven row counts';
	}

	//we don't always want the default Any row shown
	if ($show_any) {
		$return .= '
		<input type="radio" name="'.$select_name.'" value=""'.(($checked_val == '')? ' checked="checked"' : '').' />&nbsp;'.$text_any.'<br />';
	}
	//we don't always want the default 100 row shown
	if ($show_100) {
		$return .= '
		<input type="radio" name="'.$select_name.'" value="100"'.(($checked_val == 100)? ' checked="checked"' : '').' />&nbsp;'.$text_100.'<br />';
	}

	$checked_found = false;

	for ($i = 0; $i < $rows; $i++) {
		//  uggh - sorry - don't show the 100 row
		//  if it was shown above, otherwise do show it
		if (($vals[$i] != '100') || ($vals[$i] == '100' && !$show_100)) {
			$return .= '
				<input type="radio" id="'.$select_name.'_'.$vals[$i].'" name="'.$select_name.'" value="'.$vals[$i].'"';
			if ((string)$vals[$i] == (string)$checked_val) {
				$checked_found = true;
				$return .= ' checked="checked"';
			}
			$return .= ' />&nbsp;'.htmlspecialchars($texts[$i]).'<br />';
		}
	}
	//
	//	If the passed in "checked value" was never "SELECTED"
	//	we want to preserve that value UNLESS that value was 'xzxz', the default value
	//
	if (!$checked_found && $checked_val != 'xzxz' && $checked_val && $checked_val != 100) {
		$return .= '
		<input type="radio" value="'.$checked_val.'" checked="checked" />&nbsp;'._('No Change').'<br />';
	}

	return $return;
}

/**
 * html_get_tooltip_description() - Get the tooltip description of the element
 *
 * @param	string	$element_name	element name
 * @return	string
 */

function html_get_tooltip_description($element_name) {
	global $use_tooltips;
	if (!$use_tooltips) {
		return '';
	}
	switch ($element_name) {
		case 'assigned_to':
			return _('This drop-down box represents the person to which a tracker item is assigned.');
		case 'status_id':
			return _('This drop-down box represents the current status of a tracker item.')
				.'<br /><br />'
				._('You can set the status to “Pending” if you are waiting for a response from the tracker item author.  When the author responds the status is automatically reset to that of “Open”. Otherwise, if the author does not respond with an admin-defined amount of time (default is 14 days) then the item is given a status of “Deleted”.');
		case 'category':
			return _('Tracker category');
		case 'group':
			return _('Tracker group');
		case 'sort_by':
			return _('The Sort By option allows you to determine how the browse results are sorted.')
				.'<br /><br />'
				._('You can sort by ID, Priority, Summary, Open Date, Close Date, Submitter, or Assignee.  You can also have the results sorted in Ascending or Descending order.');
		case 'new_artifact_type_id':
			return _('The Data Type option determines the type of tracker item this is.  Since the tracker rolls into one the bug, patch, support, etc... managers you need to be able to determine which one of these an item should belong.')
				.'<br /><br />'
				._('This has the added benefit of enabling an admin to turn a support request into a bug.');
		case 'priority':
			return _('The priority option allows a user to define a tracker item priority (ranging from 1-Lowest to 5-Highest).')
				.'<br /><br />'
				._('This is especially helpful for bugs and support requests where a user might find a critical problem with a project.');
		case 'resolution':
			return _('Resolution');
		case 'summary':
			return _('The summary text-box represents a short tracker item summary. Useful when browsing through several tracker items.');
		case 'canned_response':
			return _('The canned response drop-down represents a list of project admin-defined canned responses to common support or bug submission.')
				.'<br /><br />'
				._('If you are a project admin you can click the “Manage Canned Responses” link to define your own canned responses');
		case 'comment':
			return _('Anyone can add here comments to give additional information, answers and solutions. Please, be as precise as possible to avoid misunderstanding. If relevant, screenshots or documents can be added as attached files.');
		case 'description':
			return _('Enter the complete description.')
				.'<br/><br/>'
				."<div align='left' >"
				._("<strong>Editing tips:</strong><br/><strong>http,https or ftp</strong>: Hyperlinks.<br/><strong>[#NNN]</strong>: Tracker id NNN.<br/><strong>[TNNN]</strong>: Task id NNN.<br/><strong>[wiki:&lt;pagename&gt;]</strong>: Wiki page.<br/><strong>[forum:&lt;msg_id&gt;]</strong>: Forum post.")
				.'</div>';
		case 'attach_file':
			return _('When you wish to attach a file to a tracker item you must check this checkbox before submitting changes.');
		case 'monitor':
			return _('You can monitor or un-monitor this item by clicking the “Monitor” button.')
				.' <br /><br />'
				._('<strong>Note!</strong> this will send you additional email. If you add comments to this item, or submitted, or are assigned this item, you will also get emails for those reasons as well!');
		case 'vote':
			return _('You can cast your vote for a Tracker Item to aid Project Management to decide which features to prioritise, and retract votes at any time. Please use this functionality sparingly, as it loses its meaning if you vote on *every* item.');
		case 'votes':
			return _('This metric displays the number of people who can *currently* vote for features in this tracker, and how many of them did so. (This means historic votes of people no longer allowed to vote, while not lost, do not play into the numbers displayed.)');
		default:
			return '';
	}
}

function html_use_jquery() {
	use_javascript('/scripts/jquery/jquery-1.10.2.js');
}

function html_use_storage() {
	html_use_jquery();
	use_javascript('/scripts/jquery-storage/jquery.Storage.js');
}

function html_use_simplemenu() {
	html_use_jquery();
	use_javascript('/scripts/jquery-simpletreemenu/js/jquery-simpleTreeMenu-1.5.0.js');
	use_stylesheet('/scripts/jquery-simpletreemenu/css/jquery-simpleTreeMenu-1.5.0.css');
}

function html_use_coolfieldset() {
	html_use_jquery();
	use_javascript('/scripts/coolfieldset/js/jquery.coolfieldset.js');
	use_javascript('/js/jquery-common.js');
	use_stylesheet('/scripts/coolfieldset/css/jquery.coolfieldset.css');
}

function html_use_jqueryui() {
	html_use_jquery();
	use_javascript('/scripts/jquery-ui/js/jquery-ui-1.10.4.js');
}

function html_use_jqueryjqplot() {
	html_use_jquery();
	use_javascript('/scripts/jquery-jqplot/jquery.jqplot.js');
	use_stylesheet('/scripts/jquery-jqplot/jquery.jqplot.css');
}

function html_use_jqueryjqplotpluginCanvas() {
	html_use_jqueryjqplot();
	use_javascript('/scripts/jquery-jqplot/plugins/jqplot.canvasTextRenderer.js');
	use_javascript('/scripts/jquery-jqplot/plugins/jqplot.canvasAxisLabelRenderer.js');
	use_javascript('/scripts/jquery-jqplot/plugins/jqplot.canvasAxisTickRenderer.js');
	use_javascript('/scripts/jquery-jqplot/plugins/jqplot.categoryAxisRenderer.js');
}

function html_use_jqueryjqplotpluginBar() {
	html_use_jqueryjqplot();
	use_javascript('/scripts/jquery-jqplot/plugins/jqplot.barRenderer.js');
	use_javascript('/scripts/jquery-jqplot/plugins/jqplot.pointLabels.js');
	use_javascript('/scripts/jquery-jqplot/plugins/jqplot.categoryAxisRenderer.js');
}

function html_use_jqueryjqplotpluginPie() {
	html_use_jqueryjqplot();
	use_javascript('/scripts/jquery-jqplot/plugins/jqplot.pieRenderer.js');
}

function html_use_jqueryjqplotpluginhighlighter() {
	html_use_jqueryjqplot();
	use_javascript('/scripts/jquery-jqplot/plugins/jqplot.highlighter.js');
}

function html_use_jqueryjqplotplugindateAxisRenderer() {
	html_use_jqueryjqplot();
	use_javascript('/scripts/jquery-jqplot/plugins/jqplot.dateAxisRenderer.js');
}

function html_use_jqueryteamworkgantt() {
	html_use_jqueryui();
	use_javascript('/scripts/jquery-teamwork-gantt/libs/jquery.livequery.min.js');
	use_javascript('/scripts/jquery-teamwork-gantt/libs/jquery.timers.js');
	use_javascript('/scripts/jquery-teamwork-gantt/libs/platform.js');
	use_javascript('/scripts/jquery-teamwork-gantt/libs/date.js');
	use_javascript('/scripts/jquery-teamwork-gantt/libs/date.js');
	use_javascript('/scripts/jquery-teamwork-gantt/libs/i18nJs.js');
	use_javascript('/scripts/jquery-teamwork-gantt/libs/dateField/jquery.dateField.js');
	use_javascript('/scripts/jquery-teamwork-gantt/libs/JST/jquery.JST.js');
	use_javascript('/scripts/jquery-teamwork-gantt/ganttUtilities.js');
	use_javascript('/scripts/jquery-teamwork-gantt/ganttTask.js');
	use_javascript('/scripts/jquery-teamwork-gantt/ganttDrawer.js');
	use_javascript('/scripts/jquery-teamwork-gantt/ganttGridEditor.js');
	use_javascript('/scripts/jquery-teamwork-gantt/ganttMaster.js');
	use_stylesheet('/scripts/jquery-teamwork-gantt/platform.css');
	use_stylesheet('/scripts/jquery-teamwork-gantt/libs/dateField/jquery.dateField.css');
	use_stylesheet('/scripts/jquery-teamwork-gantt/gantt.css');
}

function html_use_jquerysplitter() {
	html_use_jquery();
	use_javascript('/scripts/jquery-splitter/js/jquery.splitter-0.8.0.js');
	use_stylesheet('/scripts/jquery-splitter/css/jquery.splitter.css');
}

function html_use_jqueryautoheight() {
	html_use_jquery();
	use_javascript('/scripts/jquery-auto-height/jquery.iframe-auto-height.plugin.1.9.3.js');
}

/**
 * html_build_select_box_from_arrays() - Takes two arrays, with the first array being the "id" or value and the other
 * array being the text you want displayed.
 *
 * The infamous '100 row' has to do with the SQL Table joins done throughout all this code.
 * There must be a related row in users, categories, et	, and by default that
 * row is 100, so almost every pop-up box has 100 as the default
 * Most tables in the database should therefore have a row with an id of 100 in it so that joins are successful
 *
 * @param	array		$vals		The ID or value
 * @param	array		$texts		Text to be displayed
 * @param	string		$select_name	Name to assign to this form element
 * @param	string		$checked_val	The item that should be checked
 * @param	bool		$show_100	Whether or not to show the '100 row'
 * @param	string		$text_100	What to call the '100 row' defaults to none
 * @param	bool		$show_any	Whether or not to show the 'Any row'
 * @param	string		$text_any	What to call the 'Any row' defaults to any
 * @param	bool|array	$allowed	Array of all allowed values from the full list.
 * @return	string
 */
function html_build_select_box_from_arrays($vals, $texts, $select_name, $checked_val = 'xzxz',
					   $show_100 = true, $text_100 = 'none',
					   $show_any = false, $text_any = 'any', $allowed = false) {
	$have_a_subelement = false;
	$return = '';

	$rows = count($vals);
	if (count($texts) != $rows) {
		$return .= _('Error: uneven row counts');
	}

	//TODO: remove this ugly ack to get something more generic...
	$title = html_get_tooltip_description($select_name);
	$id = '';
	if ($title) {
		$id = 'tracker-'.$select_name.'"';
		if (preg_match('/\[\]/', $id)) {
			$id = '';
		}
	}

	$return .= html_ao('select', array('id' => $id, 'name' => $select_name, 'title' => util_html_secure($title)));

	//we don't always want the default Any row shown
	if ($show_any) {
		$attrs = array('value' => '');
		if ($checked_val)
			$attrs['selected'] = 'selected';
		$return .= html_e('option', $attrs, util_html_secure($text_any), false);
		$have_a_subelement = true;
	}
	//we don't always want the default 100 row shown
	if ($show_100) {
		if ($text_100 == 'none') {
			$text_100 = _('None');
		}
		$attrs = array('value' => 100);
		if ($checked_val)
			$attrs['selected'] = 'selected';
		$return .= html_e('option', $attrs, util_html_secure($text_100), false);
		$have_a_subelement = true;
	}

	$checked_found = false;

	for ($i = 0; $i < $rows; $i++) {
		//  uggh - sorry - don't show the 100 row
		//  if it was shown above, otherwise do show it
		if (($vals[$i] != '100') || ($vals[$i] == '100' && !$show_100)) {
			$attrs = array();
			$attrs['value'] = util_html_secure($vals[$i]);
			if ((string)$vals[$i] == (string)$checked_val) {
				$checked_found = true;
				$attrs['selected'] = 'selected';
			}
			if (is_array($allowed) && !in_array($vals[$i], $allowed)) {
				$attrs['disabled'] = 'disabled';
				$attrs['class'] = 'option_disabled';
			}
			$return .= html_e('option', $attrs, util_html_secure($texts[$i]));
			$have_a_subelement = true;
		}
	}
	//
	//	If the passed in "checked value" was never "SELECTED"
	//	we want to preserve that value UNLESS that value was 'xzxz', the default value
	//
	if (!$checked_found && $checked_val != 'xzxz' && $checked_val && $checked_val != 100) {
		$return .= html_e('option', array('value' => util_html_secure($checked_val), 'selected' => 'selected'), _('No Change'), false);
		$have_a_subelement = true;
	}

	if (!$have_a_subelement) {
		/* <select></select> without <option/> in between is invalid */
		return '<!-- select without options -->'."\n";
	}

	$return .= html_ac(html_ap() -1);
	return $return;
}

/**
 * html_build_select_box() - Takes a result set, with the first column being the "id" or value and
 * the second column being the text you want displayed.
 *
 * @param	resource	$result		The result set
 * @param	string		$name		Text to be displayed
 * @param	string		$checked_val	The item that should be checked
 * @param	bool		$show_100	Whether or not to show the '100 row'
 * @param	string		$text_100	What to call the '100 row'.  Defaults to none.
 * @param	bool		$show_any	Whether or not to show the 'Any row'
 * @param	string		$text_any	What to call the 'Any row' defaults to any
 * @param	bool		$allowed	Unused
 * @return	string
 */
function html_build_select_box($result, $name, $checked_val = "xzxz", $show_100 = true, $text_100 = 'none',
							   $show_any = false, $text_any = 'Select One', $allowed = false) {
	if ($text_100 == 'none') {
		$text_100 = _('None');
	}
	if ($text_any == 'Select One') {
		$text_any = _('Select One');
	}
	return html_build_select_box_from_arrays(util_result_column_to_array($result, 0),
											 util_result_column_to_array($result, 1),
											 $name, $checked_val, $show_100, $text_100, $show_any, $text_any);
}

/**
 * html_build_select_box_sorted() - Takes a result set, with the first column being the "id" or value and
 * the second column being the text you want displayed.
 *
 * @param	int	$result		The result set
 * @param	string	$name		Text to be displayed
 * @param	string	$checked_val	The item that should be checked
 * @param	bool	$show_100	Whether or not to show the '100 row'
 * @param	string	$text_100	What to call the '100 row'.  Defaults to none.
 * @return	string
 */
function html_build_select_box_sorted($result, $name, $checked_val = "xzxz", $show_100 = true, $text_100 = 'none') {
	if ($text_100 == 'none') {
		$text_100 = _('None');
	}
	$vals = util_result_column_to_array($result, 0);
	$texts = util_result_column_to_array($result, 1);
	array_multisort($texts, SORT_ASC, SORT_STRING, $vals);
	return html_build_select_box_from_arrays ($vals, $texts, $name, $checked_val, $show_100, $text_100);
}

/**
 * html_build_multiple_select_box() - Takes a result set, with the first column being the "id" or value
 * and the second column being the text you want displayed.
 *
 * @param	resource	$result		The result set
 * @param	string		$name		Text to be displayed
 * @param	string		$checked_array	The item that should be checked
 * @param	int		$size		The size of this box
 * @param	bool		$show_100	Whether or not to show the '100 row'
 * @param	string		$text_100	The displayed text of the '100 row'
 * @return	string
 */
function html_build_multiple_select_box($result, $name, $checked_array, $size = 8, $show_100 = true, $text_100 = 'none') {
	$checked_count = count($checked_array);
	$return = html_ao('select', array('name' => $name, 'multiple' => 'multiple', 'size' => $size));
	if ($show_100) {
		if ($text_100 == 'none') {
			$text_100 = _('None');
		}
		/*
			Put in the default NONE box
		*/
		$attrs = array('value' => 100);
		for ($j = 0; $j < $checked_count; $j++) {
			if ($checked_array[$j] == '100') {
				$attrs['selected'] = 'selected';
			}
		}
		$return .= html_e('option', $attrs, $text_100, false);
	}

	$rows = db_numrows($result);
	for ($i = 0; $i < $rows; $i++) {
		if ((db_result($result, $i, 0) != '100') || (db_result($result, $i, 0) == '100' && !$show_100)) {
			$attrs = array();
			$attrs = array('value' => db_result($result, $i, 0));
			/*
				Determine if it's checked
			*/
			$val = db_result($result, $i, 0);
			for ($j = 0; $j < $checked_count; $j++) {
				if ($val == $checked_array[$j]) {
					$attrs['selected'] = 'selected';
				}
			}
			$return .= html_e('option', $attrs, substr(db_result($result, $i, 1), 0, 35), false);
		}
	}
	$return .= html_ac(html_ap() -1);
	return $return;
}

/**
 * html_build_multiple_select_box_from_arrays() - Takes two arrays and builds a multi-select box
 *
 * @param	array	$ids		id of the field
 * @param	array	$texts		Text to be displayed
 * @param	string	$name		id of the items selected
 * @param	string	$checked_array	The item that should be checked
 * @param	int	$size		The size of this box
 * @param	bool	$show_100	Whether or not to show the '100 row'
 * @param	string	$text_100	What to call the '100 row' defaults to none.
 * @return	string
 */
function html_build_multiple_select_box_from_arrays($ids, $texts, $name, $checked_array, $size = 8, $show_100 = true, $text_100 = 'none') {
	$checked_count = count($checked_array);
	$return = html_ao('select', array('name' => $name, 'multiple' => 'multiple', 'size' => $size));
	if ($show_100) {
		if ($text_100 == 'none') {
			$text_100 = _('None');
		}
		/*
			Put in the default NONE box
		*/
		$attrs = array('value' => 100);
		for ($j = 0; $j < $checked_count; $j++) {
			if ($checked_array[$j] == '100') {
				$attrs['selected'] = 'selected';
			}
		}
		$return .= html_e('option', $attrs, $text_100, false);
	}

	$rows = count($ids);
	for ($i = 0; $i < $rows; $i++) {
		if (($ids[$i] != '100') || ($ids[$i] == '100' && !$show_100)) {
			$attrs = array();
			$attrs = array('value' => $ids[$i]);
			/*
				Determine if it's checked
			*/
			$val = $ids[$i];
			for ($j = 0; $j < $checked_count; $j++) {
				if ($val == $checked_array[$j]) {
					$attrs['selected'] = 'selected';
				}
			}
			$return .= html_e('option', $attrs, $texts[$i], false);
		}
	}
	$return .= html_ac(html_ap() -1);
	return $return;
}

/**
 * html_build_checkbox() - Render checkbox control
 *
 * @param	string	$name		name of control
 * @param	string	$value		value of control
 * @param	bool	$checked	true if control should be checked
 * @return	html code for checkbox control
 */
function html_build_checkbox($name, $value, $checked) {
	$attrs = array('id' => $name, 'name' => $name, 'value' => $value, 'type' => 'checkbox');
	if ($checked) {
		$attrs['checked'] = 'checked';
	}
	return html_e('input', $attrs);
}

/**
 * build_priority_select_box() - Wrapper for html_build_priority_select_box()
 *
 * @see html_build_priority_select_box()
 */
function build_priority_select_box($name = 'priority', $checked_val = '3', $nochange = false) {
	echo html_build_priority_select_box($name, $checked_val, $nochange);
}

/**
 * html_build_priority_select_box() - Return a select box of standard priorities.
 * The name of this select box is optional and so is the default checked value.
 *
 * @param	string	$name		Name of the select box
 * @param	string	$checked_val	The value to be checked
 * @param	bool	$nochange	Whether to make 'No Change' selected.
 */
function html_build_priority_select_box($name = 'priority', $checked_val = '3', $nochange = false) {
	$html = '<select id="tracker-'.$name.'" name="'.$name.'" title="'.util_html_secure(html_get_tooltip_description($name)).'">';
	if ($nochange) {
		$html .= '<option value="100" selected="selected" >'._('No Change').'</option>';
	}
	$labelOption = array('1 - '._('Lowest'), '2', '3', '4', '5 - '._('Highest'));
	for ($i = 1; $i <= 5; $i++) {
		$html .= '<option value="'.$i.'" ';
		if ($checked_val == $i) {
			$html .= 'selected="selected" ';
		}
		$html .= '>'.$labelOption[$i -1].'</option>';
	}
	$html .= '</select>';
	return $html;
}

/**
 * html_buildcheckboxarray() - Build an HTML checkbox array.
 *
 * @param	array	$options	Options array
 * @param	string	$name		Checkbox name
 * @param	array	$checked_array	Array of boxes to be pre-checked
 */
function html_buildcheckboxarray($options, $name, $checked_array) {
	$option_count = count($options);
	$checked_count = count($checked_array);

	for ($i = 1; $i <= $option_count; $i++) {
		$checked = 0;

		for ($j = 0; $j < $checked_count; $j++) {
			if ($i == $checked_array[$j]) {
				$checked = 1;
			}
		}
		echo html_e('br').html_build_checkbox($name, $value, $checked).$options[$i];
	}
}

/**
 * site_header() - everything required to handle security and
 * add navigation for user pages like /my/ and /account/
 *
 * @param	array	$params	Must contain $user_id
 */
function site_header($params) {
	global $HTML;
	/*
		Check to see if active user
		Check to see if logged in
	*/
	$HTML->header($params);
}

/**
 * site_footer() - Show the HTML site footer.
 *
 * @param	array	$params	Footer params array
 */
function site_footer($params = array()) {
	global $HTML;
	$HTML->footer($params);
}

/**
 * site_project_header() - everything required to handle
 * security and state checks for a project web page
 *
 * @param	params	array() must contain $toptab and $group
 */
function site_project_header($params) {

	/*
		Check to see if active
		Check to see if project rather than foundry
		Check to see if private (if private check if user_ismember)
	*/

	$group_id = $params['group'];

	//get the project object
	$project = group_get_object($group_id);

	if (!$project || !is_object($project)) {
		exit_no_group();
	} elseif ($project->isError()) {
		if ($project->isPermissionDeniedError()) {
			if (!session_get_user()) {
				$next = '/account/login.php?error_msg='.urlencode($project->getErrorMessage());
				if (getStringFromServer('REQUEST_METHOD') != 'POST') {
					$next .= '&return_to='.urlencode(getStringFromServer('REQUEST_URI'));
				}
				session_redirect($next);
			} else
				exit_error(sprintf(_('Project access problem: %s'), $project->getErrorMessage()), 'home');
		}
		exit_error(sprintf(_('Project Problem: %s'), $project->getErrorMessage()), 'home');
	}

	// Check permissions in case of restricted access
	session_require_perm('project_read', $group_id);

	//for dead projects must be member of admin project
	if (!$project->isActive()) {
		session_require_global_perm('forge_admin');
	}

	if (isset($params['title'])) {
		$h1 = $params['title'];
		$params['title'] = $project->getPublicName()._(': ').$params['title'];
	} else {
		$h1 = $project->getPublicName();
		$params['title'] = $project->getPublicName();
	}
	if (!isset($params['h1'])) {
		$params['h1'] = $h1;
	}

	if ($project->getDescription()) {
		$params['meta-description'] = $project->getDescription();
	}

	if (forge_get_config('use_project_tags')) {
		$res = db_query_params('SELECT name FROM project_tags WHERE group_id = $1', array($group_id));
		if ($res && db_numrows($res) > 0) {
			while ($row = db_fetch_array($res)) {
				$array[] = $row['name'];
			}
			$params['meta-keywords'] = htmlspecialchars(join(', ', $array));
		}
	}

	site_header($params);
}

/**
 * site_project_footer() - currently a simple shim
 * that should be on every project page,  rather than
 * a direct call to site_footer() or theme_footer()
 *
 * @param	array	$params	array() empty
 */
function site_project_footer($params = array()) {
	site_footer($params);
}

/**
 * site_user_header() - everything required to handle security and
 * add navigation for user pages like /my/ and /account/
 *
 * @param	array	$params	array() must contain $user_id
 */
function site_user_header($params) {
	global $HTML;

	/*
		Check to see if active user
		Check to see if logged in
	*/
	site_header($params);
	echo $HTML->beginSubMenu();
	$arr_t = array();
	$arr_l = array();
	$arr_attr = array();

	$arr_t[] = _('My Personal Page');
	$arr_l[] = '/my/';
	$arr_attr[] = array('title' => _('View your personal page, a selection of widgets to follow the informations from projects.'));

	if (forge_get_config('use_tracker')) {
		$arr_t[] = _('My Trackers Dashboard');
		$arr_l[] = '/my/dashboard.php';
		$arr_attr[] = array('title' => _('View your tasks and artifacts.'));
	}

	if (forge_get_config('use_diary')) {
		$arr_t[] = _('My Diary and Notes');
		$arr_l[] = '/my/diary.php';
		$arr_attr[] = array('title' => _('Manage your diary. Add, modify or delete your notes.'));
	}

	$arr_t[] = _('My Account');
	$arr_l[] = '/account/';
	$arr_attr[] = array('title' => _('Manage your account. Change your password, select your preferences.'));

	if (!forge_get_config('project_registration_restricted')
			|| forge_check_global_perm('approve_projects', '')) {
		$arr_t[] = _('Register Project');
		$arr_l[] = '/register/';
		$arr_attr[] = array('title' => _('Register a new project in forge, following the workflow.'));
	}

	echo ($HTML->printSubMenu($arr_t, $arr_l, $arr_attr));
	if (plugin_hook_listeners("usermenu") > 0) {
		echo $HTML->subMenuSeparator();
	}
	plugin_hook("usermenu");
	echo $HTML->endSubMenu();
}

/**
 * site_user_footer() - currently a simple shim that should be on every user page,
 * rather than a direct call to site_footer() or theme_footer()
 *
 * @param	array	$params	array() empty
 */
function site_user_footer($params = array()) {
	site_footer($params);
}

/**
 * html_clean_hash_string() - Remove noise characters from hex hash string
 *
 * Thruout SourceForge, URLs with hexadecimal hash string parameters
 * are being sent via email to request confirmation of user actions.
 * It was found that some mail clients distort this hash, so we take
 * special steps to encode it in the way which help to preserve its
 * recognition. This routine
 *
 * @param	string	$hashstr	required hash parameter as received from browser
 * @return	string	pure hex string
 */
function html_clean_hash_string($hashstr) {

	if (substr($hashstr, 0, 1) == "_") {
		$hashstr = substr($hashstr, 1);
	}

	if (substr($hashstr, strlen($hashstr) - 1, 1) == ">") {
		$hashstr = substr($hashstr, 0, strlen($hashstr) - 1);
	}

	return $hashstr;
}

function relative_date($date) {
	$delta = max(time() - $date, 0);
	if ($delta < 60)
		return sprintf(ngettext('%d second ago', '%d seconds ago', $delta), $delta);

	$delta = round($delta / 60);
	if ($delta < 60)
		return sprintf(ngettext('%d minute ago', '%d minutes ago', $delta), $delta);

	$delta = round($delta / 60);
	if ($delta < 24)
		return sprintf(ngettext('%d hour ago', '%d hours ago', $delta), $delta);

	$delta = round($delta / 24);
	if ($delta < 7)
		return sprintf(ngettext('%d day ago', '%d days ago', $delta), $delta);

	$delta = round($delta / 7);
	if ($delta < 4)
		return sprintf(ngettext('%d week ago', '%d weeks ago', $delta), $delta);

	return date(_('Y-m-d H:i'), $date);
}

/* TODO: think about beautifying output */

/**
 * html_eo() - Return proper element XHTML start tag
 *
 * @param	string	$name
 *			element name
 * @param	array	$attrs
 *			(optional) associative array of element attributes
 *			values: arrays are space-imploded;
 *			false values and empty arrays ignored
 * @return	string
 *		XHTML string suitable for echo'ing
 */
function html_eo($name, $attrs = array()) {
	global $use_tooltips, $html_autoclose_pos;
	if (!$use_tooltips && isset($attrs['title'])) {
		$attrs['title'] = '';
	}
	$rv = '';
	for ($i = 0; $i < $html_autoclose_pos; $i++) {
		$rv .= "\t";
	}
	$rv .= '<'.$name;
	foreach ($attrs as $key => $value) {
		if (is_array($value)) {
			$value = count($value) ? implode(" ", $value) : false;
		}
		if ($value === false) {
			continue;
		}
		$rv .= ' '.$key.'="'.util_html_secure($value).'"';
	}
	$rv .= '>'."\n";
	return $rv;
}

/**
 * html_e() - Return proper element XHTML start/end sequence
 *
 * @param	string	$name
 *			element name
 * @param	array	$attrs
 *		(optional) associative array of element attributes
 *			values: arrays are space-imploded;
 *			    false values and empty arrays ignored
 * @param	string	$content
 *		(optional) XHTML to be placed inside
 * @param	bool	$shortform
 *		(optional) allow short open-close form
 *		(default: true)
 * @return	string
 *		XHTML string suitable for echo'ing
 */
function html_e($name, $attrs = array(), $content = "", $shortform = true) {
	global $use_tooltips, $html_autoclose_pos;
	if (!$use_tooltips && isset($attrs['title'])) {
		$attrs['title'] = '';
	}
	$rv = '';
	$tab = '';
	for ($i = 0; $i < $html_autoclose_pos +1; $i++) {
		$tab .= "\t";
	}
	$rv .= $tab;
	$rv .= '<'.$name;
	foreach ($attrs as $key => $value) {
		if (is_array($value)) {
			$value = count($value) ? implode(" ", $value) : false;
		}
		if ($value === false) {
			continue;
		}
		$rv .= ' '.$key.'="'.util_html_secure($value).'"';
	}
	if ($content === "" && $shortform) {
		$rv .= ' />'."\n";
	} else {
		$rv .= '>';
		if (preg_match('/([\<])([^\>]{1,})*([\>])/i', $content)) {
			$rv .= "\n\t";
		}
		$rv .= $content;
		if (preg_match('/([\<])([^\>]{1,})*([\>])/i', $content)) {
			$rv .= $tab;
		}
		$rv .= '</'.$name.'>'."\n";
	}
	return $rv;
}

$html_autoclose_stack = array();
$html_autoclose_pos = 0;

/**
 * html_ap() - Return XHTML element autoclose stack position
 *
 * @return	integer
 */
function html_ap() {
	global $html_autoclose_pos;

	return $html_autoclose_pos;
}

/**
 * html_ao() - Return proper element XHTML start tag, with autoclose
 *
 * @param	string	$name
 *			element name
 * @param	array	$attrs
 *		(optional) associative array of element attributes
 *			values: arrays are space-imploded;
 *			    false values and empty arrays ignored
 * @return	string
 *		XHTML string suitable for echo'ing
 */
function html_ao($name, $attrs = array()) {
	global $html_autoclose_pos, $html_autoclose_stack;

	$html_autoclose_stack[$html_autoclose_pos++] = array(
		'name' => $name,
		'attr' => $attrs,
	);
	return html_eo($name, $attrs);
}

/**
 * html_aonce() - Return once proper element XHTML start tag, with autoclose
 *
 * @param	ref	&$sptr
			initialise this to false; will be modified
 * @param	string	$name
 *			element name
 * @param	array	$attrs
 *		(optional) associative array of element attributes
 *			values: arrays are space-imploded;
 *			    false values and empty arrays ignored
 * @return	string
 *		XHTML string suitable for echo'ing
 */
function html_aonce(&$sptr, $name, $attrs = array()) {
	if ($sptr !== false) {
		/* already run */
		return "";
	}
	$sptr = html_ap();
	return html_ao($name, $attrs);
}

/**
 * html_ac() - Return proper element XHTML end tags, autoclosing
 *
 * @param	$spos	integer
 *			stack position to return to (nothing is done if === false)
 * @throws	Exception
 * @return	string	XHTML string suitable for echo'ing
 */
function html_ac($spos) {
	global $html_autoclose_pos, $html_autoclose_stack;

	if ($spos === false) {
		/* support for html_aonce() */
		return "";
	}

	if ($html_autoclose_pos < $spos) {
		$e = "html_autoclose stack underflow; closing down to ".
			$spos." but we're down to ".$html_autoclose_pos.
			" already!";
		throw new Exception($e);
	}

	$rv = '';
	while ($html_autoclose_pos > $spos) {
		for ($i = 0; $i < $html_autoclose_pos; $i++) {
			$rv .= "\t";
		}
		--$html_autoclose_pos;
		$rv .= '</'.$html_autoclose_stack[$html_autoclose_pos]['name'].'>'."\n";
		unset($html_autoclose_stack[$html_autoclose_pos]);
	}
	return $rv;
}

/**
 * html_a_copy() - Return a copy of part of the autoclose stack
 *
 * @param	int	$spos
 *            stack position caller will return to
 * @throws	Exception
 * @return	array
 *			argument suitable for html_a_apply()
 */
function html_a_copy($spos) {
	global $html_autoclose_pos, $html_autoclose_stack;

	if ($spos === false) {
		return array();
	}

	if ($spos > $html_autoclose_pos) {
		$e = "html_autoclose stack underflow; closing down to ".
			$spos." but we're down to ".$html_autoclose_pos.
			" already!";
		throw new Exception($e);
	}

	$rv = array();
	while ($spos < $html_autoclose_pos) {
		$rv[] = $html_autoclose_stack[$spos++];
	}
	return $rv;
}

/**
 * html_a_apply() - Reopen tags based on an autoclose stack copy
 *
 * @param	array	$scopy
 *			return value from html_a_copy()
 * @return	string
 *		XHTML string suitable for echo'ing
 */
function html_a_apply($scopy) {
	/* array_reduce() would be useful here... IF IT WORKED, FFS! */
	$rv = "";
	foreach ($scopy as $value) {
		$rv .= html_ao($value['name'], $value['attr']);
	}
	return $rv;
}

/**
 * html_trove_limit_navigation_box() - displays the navigation links for paging browsing
 *
 * @param	string	$php_self		URL of the very same script
 * @param	int	$querytotalcount	total number of results
 * @param	int	$trove_browselimit	the maximum number displayed on a single page
 * @param	int	$page			current page number (starting at 1)
 * @return	string
 */
function html_trove_limit_navigation_box($php_self, $querytotalcount, $trove_browselimit, $page) {

	$html_limit = sprintf(_(' Displaying %1$s per page. Projects sorted by alphabetical order.'), $trove_browselimit).'<br/>';

	// display all the numbers
	for ($i=1;$i<=ceil($querytotalcount/$trove_browselimit);$i++) {
		$html_limit .= ' ';
		if ($page != $i) {
			$html_limit .= '<a href="'.$php_self;
			$html_limit .= '?page='.$i;
			$html_limit .= '">';
		} else $html_limit .= '<strong>';
		$html_limit .= '&lt;'.$i.'&gt;';
		if ($page != $i) {
			$html_limit .= '</a>';
		} else $html_limit .= '</strong>';
		$html_limit .= ' ';
	}
	return $html_limit;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
