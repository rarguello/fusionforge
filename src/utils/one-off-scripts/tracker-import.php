<?php
/*-
 * one-off script to import tracker items (limited)
 *
 * Copyright © 2012, 2013
 *	Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * All rights reserved.
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
 *-
 * Edit below; comments inline.  Imports a JSON generated by tracker-export
 * into a tracker, although only a very small part of the data.
 */

require `forge_get_config source_path`.'/common/include/env.inc.php';
require_once $gfcommon."include/pre.php";
require_once $gfcommon.'include/minijson.php';
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactFile.class.php';
require_once $gfwww.'tracker/include/ArtifactFileHtml.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfwww.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfwww.'tracker/include/ArtifactHtml.class.php';
require_once $gfcommon.'tracker/ArtifactCanned.class.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';

function usage($rc=1) {
	echo "Usage: .../tracker-import.php 123 <t_123.json\n" .
	    "\twhere 123 is the group_artifact_id of the tracker to append to\n";
	exit($rc);
}

if (count($argv) != 2) {
	usage();
}
$argv0 = array_shift($argv);
$argv1 = array_shift($argv);

if ($argv1 == '-h') {
	usage(0);
}
if (!($trk = util_nat0($argv1))) {
	usage();
}

/* read input and ensure it’s a JSON Array or Object */
$iv = false;
if (!minijson_decode(file_get_contents('php://stdin'), $iv)) {
	echo "input is invalid JSON: $iv\n";
	die;
}
if (!is_array($iv)) {
	echo "input top-level element is not an Array or Object\n";
	die;
}

/* validate input elements */
define('IT_STR', 0);
define('IT_NUM', 1);
define('IT_ARR', 2);

/*
 * These are the fields we require in each entry. Note how we only
 * list those we actually import; so, if you change the below code
 * to import more, list them here, too.
 */
$required_fields = array(
	array(IT_STR, "_rpl_itempermalink"),
	array(IT_STR, "details"),
	array(IT_NUM, "last_modified_date"),
	array(IT_NUM, "open_date"),
	array(IT_NUM, "priority"),
	array(IT_NUM, "submitted_by"),
	array(IT_STR, "submitted_unixname"),
	array(IT_STR, "summary"),
    );


$ic = count($iv);
echo "$ic tracker items to consider\n";

/*
 * iterate over all elements in the top-level Array or Object,
 * ensuring each is a JSON Object and has all required fields
 */
foreach ($iv as $k => $v) {
	if (!is_array($v)) {
		echo "item $k is not an Object\n";
		die;
	}
	foreach ($required_fields as $r) {
		if (!isset($v[$r[1]])) {
			echo "item $k missing required field: " . $r[1] . "\n";
			die;
		}
		switch ($r[0]) {
		case IT_STR:
			/* test for scalar (not Array or Object) Value */
			if (is_array($v[$r[1]])) {
				echo "item $k field " . $r[1] .
				    " is not a scalar!\n";
				die;
			}
			break;
		case IT_NUM:
			/* test for scalar (not Array or Object) Value */
			if (is_array($v[$r[1]])) {
				echo "item $k field " . $r[1] .
				    " is not a scalar!\n";
				die;
			}
			/* test Value for integer >= 0 */
			if (util_nat0($v[$r[1]]) === false) {
				echo "item $k field " . $r[1] .
				    " is not a positive-or-zero integer!\n";
				die;
			}
			break;
		case IT_ARR:
			/* test Value is a JSON Array or Object */
			if (!is_array($v[$r[1]])) {
				echo "item $k field " . $r[1] .
				    " is not an array!\n";
				die;
			}
			break;
		default:
			/* someone made a boo-boo editing this script */
			echo "internal error: unknown type " . $r[0] .
			    " for required field: " . $r[1] . "\n";
			die;
		}
	}
}
echo "syntactically ok\n";

/* begin the import for sure */

session_set_admin();
$now = time();

/* get the Tracker */
$at =& artifactType_get_object($trk);
if (!$at || !is_object($at) || $at->isError()) {
	echo "cannot get tracker object\n";
	die;
}

/* absolute minimum needed for creating tracker items in $at */
$extra_fields = array();
if ($at->usesCustomStatuses()) {
	$i = $at->getCustomStatusField();
	$res = db_query_params('SELECT element_id
		FROM artifact_extra_field_elements
		WHERE extra_field_id=$1
		ORDER BY element_pos ASC, element_id ASC
		LIMIT 1 OFFSET 0',
	    array($i));
	$extra_fields[$i] = db_result($res, 0, 'element_id');
}

/* now import the items, one by one */

$i = 0;
db_begin();
foreach ($iv as $k => $v) {
	echo "importing $k (" . ++$i . "/$ic)\n";
	$importData = array();

	/* get all standard data fields (we use) */

	$summary = $v["summary"];
	$details = $v["details"];
	/* assign to Nobody by default */
	$assigned_to = 100;
	$priority = $v["priority"];
	$importData['time'] = (int)$v["open_date"];

	/* take over the submitter, but only if they exist */
	if ($v["submitted_by"] != 100 && ($submitter =
	    user_get_object_by_name($v["submitted_unixname"])) &&
	    is_object($submitter) && !($submitter->isError())) {
		/* map the unixname of the submitter to our local user */
		$importData['user'] = $submitter->getID();
	} else {
		/* submitted by Nobody, though we ignore the email */
		$importData['user'] = 100;
	}

	/* prepend the old permalink in front of the details */
	$details = "Imported from: " . str_replace('#', sprintf('%d', $k),
	    $v["_rpl_itempermalink"]) . "\n\n" . $details;

	/* instantiate a new item */
	$ah = new Artifact($at);
	if (!$ah || !is_object($ah) || $ah->isError()) {
		echo "cannot get the object\n";
		db_rollback();
		die;
	}

	/* actually create the item */
	if (!$ah->create($summary, $details, $assigned_to, $priority,
	    $extra_fields, $importData)) {
		echo "cannot import: " . $ah->getErrorMessage() . "\n";
		db_rollback();
		die;
	}
	/* log the import action */
	$ah->addHistory("last-modified-before-import", date('Y-m-d H:i',
	    $v["last_modified_date"]), $now);
}
db_commit();
echo "ok\n";
