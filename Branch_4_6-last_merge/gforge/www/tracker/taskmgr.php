<?php
/**
 * GForge Task Mgr And Tracker Integration
 *
 * Copyright 2003 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */

/*existing

http://dev.gforge.org/pm/task.php?func=addartifact
	&add_artifact_id=$add_artifact_id
	&project_task_id=27
	&group_id=1
	&group_project_id=3

//add
//http://dev.gforge.org/pm/task.php?group_id=1&group_project_id=3&func=addtask
//$related_artifact_summary
//$related_artifact_id
*/

require_once('pre.php');
require_once('common/pm/ProjectGroupFactory.class');
require_once('common/pm/ProjectTaskFactory.class');

$group_project_id = getIntFromRequest('group_project_id');
$aid = getIntFromRequest('aid');

$a=new Artifact($ath,$aid);
if (!$a || !is_object($a)) {
	exit_error('ERROR','Artifact Could Not Be Created');
}

//
//	Add a relationship from this artifact to an existing task
//
if (getStringFromRequest('add_to_task')) {
	$offset = getStringFromRequest('offset');
	$_order = getStringFromRequest('_order');
	$max_rows = getStringFromRequest('max_rows');
	$set = getStringFromRequest('set');
	$_assigned_to = getStringFromRequest('_assigned_to');
	$_status = getStringFromRequest('_status');
	$_category_id = getStringFromRequest('_category_id');
	
	// $group object is created in tracker.php

	$pg=new ProjectGroup($group,$group_project_id);
	if (!$pg || !is_object($pg)) {
		exit_error('Error','Could Not Get ProjectGroup');
	} elseif ($pg->isError()) {
		exit_error('Error',$pg->getErrorMessage());
	}


	$ptf = new ProjectTaskFactory($pg);
	if (!$ptf || !is_object($ptf)) {
		exit_error('Error','Could Not Get ProjectTaskFactory');
	} elseif ($ptf->isError()) {
		exit_error('Error',$ptf->getErrorMessage());
	}

	$ptf->setup($offset,$_order,$max_rows,$set,$_assigned_to,$_status,$_category_id);
	if ($ptf->isError()) {
		exit_error('Error',$ptf->getErrorMessage());
	}

	$pt_arr =& $ptf->getTasks();
	if (!$pt_arr || $ptf->isError()) {
		exit_error('Error',$ptf->getErrorMessage());
	}

	$ath->header(array('titlevals'=>array($ath->getName()),
		'atid'=>$ath->getID()));

	echo '
		<h3>'.$Language->getText('tracker_taskmgr','build_relationships').'</h3>
		<p>
		<form name="foo" action="'. getStringFromServer('PHP_SELF') .'?func=taskmgr&group_id='.$group_id.'&atid='.$atid.'&aid='.$aid.'" method="post">
		<strong>'.$Language->getText('tracker_taskmgr','tracker_item').':</strong> [#'.$a->getID().'] '.$a->getSummary().'<p>
		<strong>'.$Language->getText('tracker_taskmgr','task_manager_project').':</strong><br />';
	echo $pg->getName().'
		<input type="hidden" name="group_project_id" value="'.$pg->getID().'">
		<p>
		<strong>'.$Language->getText('tracker_taskmgr','task').':</strong><br />
		<select name="project_task_id">';
	for ($i=0; $i<count($pt_arr); $i++) {
		echo '<option value="'.$pt_arr[$i]->getID().'">'.$pt_arr[$i]->getSummary().'</option>';
	}
	echo '</select><br />
		<input type="submit" name="done_adding" value="'.$Language->getText('tracker_taskmgr','add_relationship') . '">
		</form>';

//
//	Add the relationship and display finished message
//
} elseif (getStringFromRequest('done_adding')) {

	Header ('Location: /pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id.'&project_task_id='.$project_task_id.'&func=addartifact&add_artifact_id[]='. $a->getID() );

//
//	Create a new task and relate it to this artifact
//
} elseif (getStringFromRequest('new_task')) {

	Header ('Location: /pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id.'&func=addtask&related_artifact_summary='. urlencode($a->getSummary()) .'&related_artifact_id='. $a->getID() );

//
//	Show the list of ProjectGroups available
//
} else {

	$pgf=new ProjectGroupFactory($group);
	if (!$pgf || !is_object($pgf)) {
		exit_error('Error','Could Not Get Factory');
	} elseif ($pgf->isError()) {
		exit_error('Error',$pgf->getErrorMessage());
	}

	$pg_arr =& $pgf->getProjectGroups();
	if (!$pg_arr || $pgf->isError()) {
		exit_error('Error',$pgf->getErrorMessage());
	}

	$ath->header(array('titlevals'=>array($ath->getName()),
		'atid'=>$ath->getID()));

	echo '<h3>'.$Language->getText('tracker_taskmgr','build_relationships').'</h3>
		<p><form name="foo" action="'. getStringFromServer('PHP_SELF') .'?func=taskmgr&group_id='.$group_id.'&atid='.$atid.'&aid='.$aid.'" method="post">
		<strong>'.$Language->getText('tracker_taskmgr','tracker_item').':</strong> [#'.$a->getID().'] '.$a->getSummary().'<p>
		<strong>'.$Language->getText('tracker_taskmgr','task_manager_project').':</strong><br />
		<select name="group_project_id">';
	for ($i=0; $i<count($pg_arr); $i++) {
		echo '<option value="'.$pg_arr[$i]->getID().'">'.$pg_arr[$i]->getName().'</option>';
	}
	echo '</select><br />
		<input type="submit" name="add_to_task" value="'.$Language->getText('tracker_taskmgr','add_relation_to_task').'."><br />
		<input type="submit" name="new_task" value="'.$Language->getText('tracker_taskmgr','create_task').'">
		</form>';

}

$ath->footer(array());

?>
