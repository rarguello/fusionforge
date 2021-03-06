#! /usr/bin/php
<?php
/**
 * Fusionforge Cron Job : ssh key administration
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * Copyright (C) 2009  Sylvain Beucler
 * Copyright 2012, Franck Villaume - TrivialDev
 * Copyright 2013, Xavier Le Boëc
 * Copyright (C) 2014  Inria (Sylvain Beucler)
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

function create_authkeys($params) {
	$sshdir = $params['sshdir'];
	$sshkeys = $params['sshkeys'];
	if (!is_dir($sshdir)) {
		mkdir($sshdir, 0755);
	}
	$f = fopen("$sshdir/authorized_keys", 'w');
	fwrite($f, "# This file is automatically generated from your account settings.\n");
	fwrite($f, $sshkeys);
	fclose($f);
	chmod("$sshdir/authorized_keys", 0644);
}

$err = '';

$res = db_query_params('SELECT user_id, user_name, sshkey, deploy, deleted FROM sshkeys
                          JOIN users ON sshkeys.userid = users.user_id
			WHERE users.status = $1	AND users.unix_status = $2', array('A', 'A'));
$users = array();
while ($row = db_fetch_array($res)) {
	$user_id = $row['user_id'];
	$users[$user_id]['username'] = $row['user_name'];
	if (!$row['deleted'])
		$users[$user_id]['keys'][] = $row['sshkey'];
	if (!$row['deploy'] or $row['deleted'])
		$users[$user_id]['need_update'] = 1;
}

foreach (array_keys($users) as $user_id)
	if (!isset($users[$user_id]['need_update']))
		unset($users[$user_id]);

foreach ($users as $user_id => &$v) {
	$username = $v['username'];
	$sshkeys = array_key_exists('keys', $v) ? (join("\n", $v['keys']) . "\n") : '';

	$dir = forge_get_config('homedir_prefix').'/'.$username;
	if (util_is_root_dir($dir)) {
		$err .= _('Error: homedir_prefix/username points to root directory!');
		continue;
	}

	if(!is_dir($dir)){
		$err .=  sprintf(_("Error! homedirs.php didn't create a home directory for user %s yet"), $username);
		continue;
	}

	$params = array();
	$params['sshdir']  = forge_get_config('homedir_prefix')."/$username/.ssh";
	$params['sshkeys'] = $sshkeys;

	util_sudo_effective_user($username, "create_authkeys", $params);

	$res_update = db_query_params('UPDATE sshkeys SET deploy=1 WHERE userid = $1', array($user_id));
	$res_update = db_query_params('DELETE FROM sshkeys WHERE userid = $1 AND deleted=1', array($user_id));
}

cron_entry(15, $err);
