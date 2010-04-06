<?php

/**
 * GForge ViewCVS PHP wrapper.
 *
 * Portion of this file is inspired from the ViewCVS wrapper
 * contained in CodeX.
 * Copyright (c) Xerox Corporation, CodeX / CodeX Team, 2001,2002. All Rights Reserved.
 * http://codex.xerox.com
 *
 */

// make sure we're not compressing output if we are making a tarball
if (isset($_GET['view']) && $_GET['view'] == 'tar') {
	$no_gz_buffer=true;
}

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'scm/include/scm_utils.php';
require_once $gfwww.'scm/include/viewvc_utils.php';

if (!$sys_use_scm) {
	exit_disabled();
}

// Get the project name from query
if(getStringFromGet('root') && strpos(getStringFromGet('root'), ';') === false) {
	$projectName = getStringFromGet('root');
} else {
	$queryString = getStringFromServer('QUERY_STRING');
	if(preg_match_all('/[;]?([^\?;=]+)=([^;]+)/', $queryString, $matches, PREG_SET_ORDER)) {
		for($i = 0, $size = sizeof($matches); $i < $size; $i++) {
			$query[$matches[$i][1]] = urldecode($matches[$i][2]);
		}
		$projectName = $query['root'];
	}
}
// Remove eventual leading /root/ or root/
$projectName = ereg_replace('^..[^/]*/','', $projectName);
if (!$projectName) {
	exit_no_group();
}

// Check permissions
$Group =& group_get_object_by_name($projectName);
if (!$Group || !is_object($Group) || $Group->isError()) {
	exit_no_group();
}
if (!$Group->usesSCM()) {
	exit_error(_('Error'), _('Error - This project has turned off SCM.'));
}

// check if the scm_box is located in another server
$scm_box = $Group->getSCMBox();
//$external_scm = (gethostbyname(forge_get_config('web_host')) != gethostbyname($scm_box)); 
$external_scm = !$sys_scm_single_host;

if (session_loggedin()) {
	if (user_ismember($Group->getID())) {
		$perm = & $Group->getPermission(session_get_user());
		
		if (!($perm && is_object($perm) && $perm->isCVSReader()) && !$Group->enableAnonSCM()) {
			exit_permission_denied();
		}
	} else if (!$Group->enableAnonSCM()) {
		exit_permission_denied();
	}
	
} else if (!$Group->enableAnonSCM()) {		// user is not logged in... check if group accepts anonymous CVS
	exit_permission_denied();
}

if ($external_scm) {
	//$server_script = "/cgi-bin/viewcvs.cgi";
	$server_script = $GLOBALS["sys_path_to_scmweb"]."/viewcvs.cgi";
	// remove leading / (if any)
	$server_script = preg_replace("/^\\//", "", $server_script); 
	
	// pass the parameters passed to this script to the remote script in the same fashion
	$script_url = "http://".$scm_box."/".$server_script.$_SERVER["PATH_INFO"]."?".$_SERVER["QUERY_STRING"];
	$fh = @fopen($script_url, "r");
	if (!$fh) {
		exit_error('Error', 'Could not open script <b>'.$script_url.'</b>.');
	}
	
	// start reading the output of the script (in 8k chunks)
	$content = "";
	while (!feof($fh)) {
		$content .= fread($fh, 8192);
	}
	
	if (viewcvs_is_html()) {
		// Now, we must replace the occurencies of $server_script with this script
		// (do this only of outputting HTML)
		// We must do this because we can't pass the environment variable SCRIPT_NAME
		// to the cvsweb script (maybe this can be fixed in the future?)
		$content = str_replace("/".$server_script, $_SERVER["SCRIPT_NAME"], $content);
	}
} else {
	$unix_name = $Group->getUnixName();

	// Call to ViewCVS CGI locally (see viewcvs_utils.php)
	
	// see what type of plugin this project if using
	if ($Group->usesPlugin('scmcvs')) {
		$repos_type = 'cvs';
	} else if ($Group->usesPlugin('scmsvn')) {
		$repos_type = 'svn';
	}
	
	$content = viewcvs_execute($unix_name, $repos_type);
}

// Set content type header from the value set by ViewCVS
// No other headers are generated by ViewCVS because in generate_etags
// is set to 0 in the ViewCVS config file
$found = false;
$line = strtok($content,SEPARATOR);
while ($line && !$found) {
	if (preg_match('/^Content-Type:(.*)$/',$line,$matches)) {
		header('Content-Type:' . $matches[1]);
 		$found = true;
 	}
	$line = strtok(SEPARATOR);	
}
$content = substr($content, strpos($content,$line));

if (viewcvs_is_html()) {
	// If we output html and we found the mbstring extension, we
	// should try to encode the output of ViewCVS in UTF-8
	if (extension_loaded('mbstring')) {
		$encoding = mb_detect_encoding($content, 'UTF-8, ISO-8859-1');
		if($encoding != 'UTF-8') {
			$content = mb_convert_encoding($content, 'UTF-8', $encoding);
		}
	}
	echo $content;
} else {
	// TODO does not seem to work when allow_tar = 1 in ViewCVS conf
	// (allow to generate on the fly a tar.gz): the generated file
	// seems to be corrupted
	echo $content;
}

?>
