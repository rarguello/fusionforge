#! /usr/bin/php4 -f
<?php

//
//	This script will read in a list existing mailing lists, then add the new lists
//	and, finally, create the lists in a /tmp/mailman-aliases file
//	The /tmp/mailman-aliases file will then be read by the mailaliases.php file
//
//	DEFINE VARS FOR USING THIS SCRIPT
//
define('MAILMAN_DIR','/var/mailman/');

require ('squal_pre.php');
require ('common/include/cron_utils.php');

//
// Extract the mailing lists that already exist on the system and create
// a "list" of them for use later so we don't try to create ones that 
// already exist
//
$mailing_lists=array();
$mlists_cmd = escapeshellcmd(MAILMAN_DIR."bin/list_lists");
$err .= "Command to be executed is $mlists_cmd\n";
$fp = popen ($mlists_cmd,"r");
while (!feof($fp)) {
	$mlist = fgets($fp, 4096);
	if (stristr($mlist,"matching mailing lists") !== FALSE) {
		continue;
	}
	$mlist = trim($mlist);
	if ($mlist <> "") {
		list($listname, $listdesc) = explode(" ",$mlist);	
		$mailing_lists[] = strtolower($listname);
		$err .= "Existing mailing List $listname found\n";	
	}
}
pclose($fp);

$res=db_query("SELECT users.user_name,email,mail_group_list.list_name,
        mail_group_list.password,mail_group_list.status, 
        mail_group_list.group_list_id 
	FROM mail_group_list,users
        WHERE mail_group_list.list_admin=users.user_id
        ");
$err .= db_error();

$rows=db_numrows($res);
$err .= "$rows rows returned from query\n";

$h1 = fopen("/tmp/mailman-aliases","w");

$mailingListIds = array();

for ($i=0; $i<$rows; $i++) {
	$err .= "Processing row $i\n";
	$listadmin = db_result($res,$i,'user_name');
	$email = db_result($res,$i,'email');
	$listname = strtolower(db_result($res,$i,'list_name'));
	$listpassword = db_result($res,$i,'password');
	$grouplistid = db_result($res,$i,'group_list_id');

	if (! in_array($listname,$mailing_lists) && db_result($res,$i,'status') == MAIL__MAILING_LIST_IS_REQUESTED) {
		$err .= "Creating Mailing List: $listname\n";
		$lcreate_cmd = MAILMAN_DIR."bin/newlist -q $listname@$sys_lists_host $email $listpassword";
		$err .= "Command to be executed is $lcreate_cmd\n";
		$fp = popen($lcreate_cmd,"r");
		pclose($fp);
		$mailingListIds[] = $grouplistid;
	}
	
	if(file_exists(MAILMAN_DIR.'mail/mailman')) {
		// Mailman 2.1
		$list_str =
$listname.':              "|'.MAILMAN_DIR.'mail/mailman post '.$listname.'"'."\n"
.$listname.'-admin:        "|'.MAILMAN_DIR.'mail/mailman admin '.$listname.'"'."\n"
.$listname.'-bounces:      "|'.MAILMAN_DIR.'mail/mailman bounces '.$listname.'"'."\n"
.$listname.'-confirm:      "|'.MAILMAN_DIR.'mail/mailman confirm '.$listname.'"'."\n"
.$listname.'-join:         "|'.MAILMAN_DIR.'mail/mailman join '.$listname.'"'."\n"
.$listname.'-leave:        "|'.MAILMAN_DIR.'mail/mailman leave '.$listname.'"'."\n"
.$listname.'-owner:        "|'.MAILMAN_DIR.'mail/mailman owner '.$listname.'"'."\n"
.$listname.'-request:      "|'.MAILMAN_DIR.'mail/mailman request '.$listname.'"'."\n"
.$listname.'-subscribe:    "|'.MAILMAN_DIR.'mail/mailman subscribe '.$listname.'"'."\n"
.$listname.'-unsubscribe:  "|'.MAILMAN_DIR.'mail/mailman unsubscribe '.$listname.'"'."\n"
;
	} else {
		// Mailman < 2.1
		$list_str =
$listname.':		"|'.MAILMAN_DIR.'mail/wrapper post '.$listname.'"'."\n"
.$listname.'-admin:	"|'.MAILMAN_DIR.'mail/wrapper mailowner '.$listname.'"'."\n"
.$listname.'-request:	"|'.MAILMAN_DIR.'mail/wrapper mailcmd '.$listname.'"'."\n";
	}

	fwrite($h1,$list_str);
}

// Update status
if(!empty($mailingListIds)) {
	db_query('UPDATE mail_group_list set status='.MAIL__MAILING_LIST_IS_CREATED.' where group_list_id IN('.implode(',', $mailingListIds).')');
}

fclose($h1);

cron_entry(18,$err);

?>
