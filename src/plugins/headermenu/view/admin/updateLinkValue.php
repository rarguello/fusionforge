<?php
/**
 * headermenuPlugin Class
 *
 * Copyright 2012-2013, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

global $HTML;
global $headermenu;
global $type;

$linkId = getIntFromRequest('linkid');
?>

<script language="Javascript" type="text/javascript">//<![CDATA[
var controllerHeaderMenu;

jQuery(document).ready(function() {
	controllerHeaderMenu = new EditHeaderMenuController({
		inputHtmlCode:	jQuery('#typemenu_htmlcode'),
		inputURL:	jQuery('#typemenu_url'),
		inputURLIframe:	jQuery('#typemenu_iframe'),
		inputHeader:	jQuery('#linkmenu_headermenu'),
		inputOuter:	jQuery('#linkmenu_outermenu'),
		trHtmlCode:	jQuery('#htmlcode'),
		trUrlCode:	jQuery('#urlcode')
    });
});

//]]></script>

<?php
$linkValues = $headermenu->getLink($linkId);
if (is_array($linkValues)) {
	echo '<form method="POST" name="updateLink" action="index.php?type='.$type.'&action=updateLinkValue">';
	echo '<table><tr>';
	echo $HTML->boxTop(_('Update this link'));
	echo '<td>'._('Displayed Name').'</td><td><input name="name" type="text" maxsize="255" value="'.$linkValues['name'].'" /></td>';
	echo '</tr><tr>';
	echo '<td>'._('Description').'</td><td><input name="description" type="text" maxsize="255" value="'.$linkValues['description'].'" /></td>';
	echo '</tr><tr>';
	if ($type == 'globaladmin') {
		echo '<td>'._('Menu Location').'</td><td>';
		$vals = array('headermenu', 'outermenu');
		$texts = array('headermenu', 'outermenu');
		$select_name = 'linkmenu';
		echo html_build_radio_buttons_from_arrays($vals, $texts, $select_name, $linkValues['linkmenu'], false);
		echo '</td>';
		echo '</tr><tr>';
	}
	echo '<td>'._('Menu Type').'</td><td>';
	if ($type == 'projectadmin') {
		$texts = array('URL', 'URL as iframe', 'New Page');
		$vals = array('url', 'iframe', 'htmlcode');
	}
	if ($type == 'globaladmin') {
		$texts = array('URL', 'New Page');
		$vals = array('url', 'htmlcode');
	}
	$select_name = 'typemenu';
	echo html_build_radio_buttons_from_arrays($vals, $texts, $select_name, $linkValues['linktype'], false);
	echo '</td>';
	echo '</tr><tr id="htmlcode" style="display:none">';
	echo '<td>'._('Your HTML Code.').'</td><td>';
	$GLOBALS['editor_was_set_up'] = false;
	$body = $linkValues['htmlcode'];
	$params['name'] = 'htmlcode';
	$params['body'] = $body;
	$params['width'] = "800";
	$params['height'] = "500";
	$params['user_id'] = user_getid();
	plugin_hook("text_editor", $params);
	if (!$GLOBALS['editor_was_set_up']) {
		echo '<textarea name="htmlcode" rows="5" cols="80">'.$body.'</textarea>';
	}
	unset($GLOBALS['editor_was_set_up']);
	echo '</td></tr><tr id="urlcode"  style="display:none" >';
	echo '<td>'._('URL').'</td><td><input name="link" type="text" maxsize="255" value="'.$linkValues['url'].'" /></td>';
	echo '</tr><tr>';
	echo '<td>';
	echo '<input type="hidden" name="linkid" value="'.$linkId.'" />';
	echo '<input type="submit" value="'. _('Update') .'" />';
	echo '<a href="/plugins/'.$headermenu->name.'/?type='.$type.'"><input type="button" value="'. _('Cancel') .'" /></a>';
	echo '</td>';
	echo $HTML->boxBottom();
	echo '</tr></table>';
	echo '</form>';
} else {
	$error_msg = _('Cannot retrieve value for this link:').' '.$linkId;
	session_redirect('plugins/'.$headermenu->name.'/?type='.$type.'&error_msg='.urlencode($error_msg));
}
