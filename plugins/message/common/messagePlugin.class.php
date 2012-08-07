<?php
/**
 * FusionForge Plugin Message Class
 *
 * Copyright 2009 (c) Alain Peyrat <alain.peyrat@alcatel-lucent.com>
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

/**
 * The messagePlugin class.
 *
 */

class messagePlugin extends Plugin {

	function messagePlugin () {
		$this->Plugin() ;
		$this->name = "message" ;
		$this->text = _('Message');
		$this->hooks[] = 'message';
		$this->hooks[] = 'htmlhead';
		$this->hooks[] = 'site_admin_option_hook';
	}

	function htmlhead() {
		html_use_jquery();
		use_javascript('/plugins/message/js/message.js');
	}

	function site_admin_option_hook() {
		echo '<li>' . util_make_link ('/plugins/message/index.php', _('Configure Global Message')) . '</li>';
	}

	function message() {
		$res = db_query_params('SELECT message FROM plugin_message', array());
		if ($res && db_numrows($res)>0) {
			echo '<div id="message_box">';
			echo '<img id="message_close" style="float:right;cursor:pointer"  src="/themes/acos/images/ic/close.png" alt="'._('Close').'" />';
			echo db_result($res, 0, 'message');
			echo '</div>';
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End: