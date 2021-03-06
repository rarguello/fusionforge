<?php
/**
 *
 * Copyright 2012, Alain Peyrat
 * Copyright 2014, Franck Villaume - TrivialDev
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

require_once dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'forum/ForumStorage.class.php';

ini_set('memory_limit', -1);
ini_set('max_execution_time', 0);

$data = forge_get_config('data_path');
if (!is_dir($data)) {
	system("mkdir -p $data");
	system("chmod 0755 $data");
}
if (!is_dir("$data/forum")) {
	system("mkdir $data/forum");
	system("chmod 0700 $data/forum");
}

$fs = new ForumStorage();
$tmp = tempnam('/tmp', 'docman');

$res = db_query_params('SELECT attachmentid FROM forum_attachment where filedata != $1', array(0));
if (!$res) {
	echo 'UPGRADE ERROR: '.db_error();
	exit(1);
}

while($row = db_fetch_array($res)) {
	$res2 = db_query_params('SELECT filedata FROM forum_attachment WHERE attachmentid = $1',
		array($row['attachmentid'])) ;
	$row2 = db_fetch_array($res2);
	$data = base64_decode($row2['filedata']);
	// Not using column 'filesize', since we saw 2 installs where it was wrong
	$size = strlen($data); // strlen == nb of bytes, not chars
	$ret = file_put_contents($tmp, $data);
	if ($ret === false) {
		echo "UPGRADE ERROR: file_put_contents($tmp) error: returned false\n";
		$fs->rollback();
		exit(1);
	}
	if ($ret != $size) {
		echo "UPGRADE ERROR: file_put_contents($tmp) size error: ($ret != ".$size.")\n";
		$fs->rollback();
		exit(1);
	}
	$ret = $fs->store($row['attachmentid'], $tmp);
	if (!$ret) {
		echo "UPGRADE ERROR: $ret: ".$fs->getErrorMessage()."\n";
		$fs->rollback();
		exit(1);
	}
	$fs->commit();
	db_query_params('UPDATE forum_attachment set filedata = $1 where attachmentid = $2', array(0, $row['attachmentid']));
}

$res = db_query_params('SELECT attachmentid FROM forum_pending_attachment where filedata != $1', array(0));
if (!$res) {
	echo 'UPGRADE ERROR: '.db_error();
	exit(1);
}

while($row = db_fetch_array($res)) {
	$res2 = db_query_params('SELECT filedata FROM forum_pending_attachment WHERE attachmentid = $1',
		array($row['attachmentid'])) ;
	$row2 = db_fetch_array($res2);
	$data = base64_decode($row2['filedata']);
	// Not using column 'filesize', since we saw 2 installs where it was wrong
	$size = strlen($data); // strlen == nb of bytes, not chars
	$ret = file_put_contents($tmp, $data);
	if ($ret === false) {
		echo "UPGRADE ERROR: file_put_contents($tmp) error: returned false\n";
		$fs->rollback();
		exit(1);
	}
	if ($ret != $size) {
		echo "UPGRADE ERROR: file_put_contents($tmp) size error: ($ret != ".$size.")\n";
		$fs->rollback();
		exit(1);
	}
	$ret = $fs->store($row['attachmentid'], $tmp);
	if (!$ret) {
		echo "UPGRADE ERROR: $ret: ".$fs->getErrorMessage()."\n";
		$fs->rollback();
		exit(1);
	}
	$fs->commit();
	db_query_params('UPDATE forum_pending_attachment set filedata = $1 where attachmentid = $2', array(0, $row['attachmentid']));
}

system("chown -R ".forge_get_config('apache_user').':'.forge_get_config('apache_group')." $data/forum");

echo "SUCCESS\n";
