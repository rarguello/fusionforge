<?php
/**
 * Site Mailings Subscription Maintenance page
 *
 * This page is used to maintain site mailings (currently, just
 * unsubscribe specific user).
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

global $HTML;

session_require_global_perm ('forge_admin');

$user_name = getStringFromRequest('user_name');
$pattern   = getStringFromRequest('pattern');

if (getStringFromRequest('submit') && $user_name) {
	$type = getStringFromRequest('type');

	if (!$type) {

		/*
				Show form for unsubscription type selection
		*/

		site_admin_header(array('title'=>_('Site Mailings Subscription Maintenance')));
		?>

		<h2><?php echo _('Unsubscribe user:'); ?> <?php echo $user_name; ?></h2>
		<p>
		<?php echo _('You can unsubscribe user either from admin-initiated sitewide mailings or from all site mailings (admin-initiated and automated mailings, like forum and file release notifications).'); ?>
		</p>
		<form action="<?php echo util_make_uri('/admin/unsubscribe.php'); ?>" method="post">
		<input type="hidden" name="user_name" value="<?php echo $user_name?>" />
		<?php echo _('Unsubscription type:').html_build_select_box_from_arrays(
			array(_('MAIL'),_('All')),
			array(_('Admin-initiated mailings'),_('All site mailings')),
			'type',false,false
		); ?>
		<input type="submit" name="submit" value="<?php echo _('Unsubscribe'); ?>" />
		</form>

		<?php
		site_admin_footer();
		exit();
	} else {

		/*
			Perform unsubscription
		*/

		$u = user_get_object_by_name($user_name);
		if (!$u || !is_object($u)) {
			exit_error(_('Could Not Get User'),'home');
		} elseif ($u->isError()) {
			exit_error($u->getErrorMessage(),'home');
		}

		if (!$u->unsubscribeFromMailings($type=='ALL' ? 1 : 0)) {
			exit_error(_('Could not unsubscribe user: ').$u->getErrorMessage(),'home');
		}

		$feedback .= _('User unsubscribed');
	}
}

site_admin_header(array('title'=>_('Site Mailings Subscription Maintenance')));

?>

<p>
<?php printf(_('Use field below to find users which match given pattern with the %s username, real name, or email address (substring match is preformed, use \'%%\' in the middle of pattern to specify 0 or more arbitrary characters). Click on the username to unsubscribe user from site mailings (new form will appear).'), forge_get_config ('forge_name')); ?>
</p>

<form action="<?php echo util_make_uri('/admin/unsubscribe.php') ?>" method="post">
<?echo _('Pattern:'); ?><input type="text" name="pattern" value="<?php echo $pattern; ?>" />
<input type="submit" name="submit" value="<?php echo _('Show users matching pattern'); ?>" />
</form>

<?php

if ($pattern) {
	$res = db_query_params('SELECT *
				FROM users
				WHERE lower(user_name) LIKE $1
				OR lower(realname) LIKE $1
				OR lower(email) LIKE $1',
				array(strtolower("%$pattern%")));

	$title=array();
	$title[]='&nbsp;';
	$title[]=_('User Id');
	$title[]=_('User Name');
	$title[]=_('Real Name');
	$title[]=_('Email');
	$title[]=_('Site Mail.');
	$title[]=_('Comm. Mail.');

	echo $HTML->listTableTop($title);
	$i = 0 ;
	while ($row = db_fetch_array($res)) {
		$cells = array();
		$cells[][] = '&nbsp;';
		$cells[][] = $row['user_id'];
		$cells[][] = util_make_link('/admin/unsubscribe.php?submit=1&user_name='.$row['user_name'], $row['user_name']);
		$cells[][] = $row['realname'];
		$cells[][] = $row['email'];
		$cells[][] = $row['mail_siteupdates'];
		$cells[][] = $row['mail_va'];
		echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
	}
	echo $HTML->listTableBottom();
}

site_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
