<?php
/**
 * GForge Plugin Activate / Deactivate Page
 *
 * @version 
 * @author 
 * @copyright 
 * Copyright 2005 GForge, LLC
 * http://gforge.org/
 *
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../env.inc.php');
require_once('pre.php');
require_once('www/admin/admin_utils.php');

site_admin_header(array('title'=>_('Site Admin')));

?>

<script type="text/javascript">

	function change(url,plugin)
	{
		field = document.theform.elements[plugin];
		if (field.checked) {
			window.location=(url + "&init=yes");
		} else {
			window.location=(url);
		}
	}

</script>

<form name="theform" action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="GET">
<?php

if (getStringFromRequest('update')) {
	$pluginname = getStringFromRequest('update');
	
	if ((getStringFromRequest('action')=='deactivate')) {
		if (getStringFromRequest('delusers')) {
			$sql = "DELETE FROM user_plugin WHERE plugin_id = (SELECT plugin_id FROM plugins WHERE plugin_name = '$pluginname')";
			$res = db_query($sql);
			if (!$res) {
				exit_error("SQL ERROR",db_error());
			} else {
				$feedback .= $Language->getText('pluginman','userdeleted',db_affected_rows($res));
			}
		}
		if (getStringFromRequest('delgroups')) {
			$sql = "DELETE FROM group_plugin WHERE plugin_id = (SELECT plugin_id FROM plugins WHERE plugin_name = '$pluginname')";
			$res = db_query($sql);
			if (!$res) {
				exit_error("SQL ERROR",db_error());
			} else {
				$feedback .= $Language->getText('pluginman','groupdeleted',db_affected_rows($res));
			}
		}
		$sql = "DELETE FROM plugins WHERE plugin_name = '$pluginname'";
		$res = db_query($sql);
		if (!$res) {
			exit_error("SQL ERROR",db_error());
		} else {
			$feedback = sprintf(_('Plugin %1$s updated Successfully'), $pluginname);
			if (is_dir($sys_plugins_path . $pluginname . '/www')) { // if the plugin has a www dir delete the link to it
				chdir('../plugins');
				if (file_exists($pluginname)) {
					system('rm ' . $pluginname,$result);
				} 
				if (file_exists('/etc/gforge/plugins/'.$pluginname)) {
					if (!chdir('/etc/gforge/plugins')) {
						$result2 = 1;
					} else {
						system('rm ' . $pluginname,$result2); // the apache group or user should have write perms in /etc/gforge/plugins folder...
					}					
				}
				if ($result!=0) {
					$feedback .= _('<br>Soft link wasn\'t removed in www/plugins folder, please do so manually.');
				}
				if ($result2!=0) {
					$feedback .= _('MISSINGTEXT:pluginman/successnodeleteconfig:TEXTMISSING');
				}
			}			
		}
	} else {
		$sql = "INSERT INTO plugins (plugin_name,plugin_desc) VALUES ('$pluginname','This is the $pluginname plugin')";
		$res = db_query($sql);
		if (!$res) {
			exit_error("SQL ERROR",db_error());
		} else {
			$feedback = sprintf(_('Plugin %1$s updated Successfully'), $pluginname);
			if (is_dir($sys_plugins_path . $pluginname . '/www')) { // if the plugin has a www dir make a link to it
				chdir('../plugins');
				$return_value = symlink($sys_plugins_path . $pluginname . '/www',$pluginname); // the apache group or user should have write perms the plugins folder...
				if (!chdir('/etc/gforge/plugins')) {
					$return_value2 = false;
				} else {
					if (is_dir($sys_plugins_path . $pluginname . '/etc/plugins/' . $pluginname)) {
						$return_value2 = symlink($sys_plugins_path . $pluginname . '/etc/plugins/' . $pluginname,$pluginname); // the apache group or user should have write perms in /etc/gforge/plugins folder...
					} else {
						//doesn�t have a config file, but that�s ok
						$return_value2 = true;
					}
				}
				if (!$return_value) {
					$feedback .= _('<br>Soft link to www couldn\'t be created. Check the write permissions for apache in gforge www/plugins dir or create the link manually.');
				}
				if (!$return_value2) {
					$feedback .= sprintf(_('<br>Config file could not be linked to etc/gforge/plugins/%1$s. Check the write permissions for apache in /etc/gforge/plugins or create the link manually.'), $pluginname);
				}
			}
			if (getStringFromRequest('init')) {
				// now we�re going to check if there�s a XX-init.sql file and run it
				if (is_file($sys_plugins_path . $pluginname . '/db/' . $pluginname . '-init.sql')) {
					$arch = file_get_contents($sys_plugins_path . $pluginname . '/db/' . $pluginname . '-init.sql');
					$arch = preg_replace('/(INSERT INTO plugins.*$)/','',$arch); // remove the line that inserts into plugins table, we are already doing that (and this would return error otherwise)
					$res = db_query($arch);
					if (!$res) {
						$feedback .= _('MISSINGTEXT:pluginman/successiniterror:TEXTMISSING');
						$feedback .= '<br>Database said: '.db_error();
					}
				}	
				//we check for a php script	
				if (is_file($sys_plugins_path . $pluginname . '/script/' . $pluginname . '-init.php')) {
				include($sys_plugins_path . $pluginname . '/script/' . $pluginname . '-init.php');		
				} else {
					
				}
			}
		}
	}

}

echo $feedback.'<br>';
echo _('Here you can activate / deactivate Site wide plugins which are in the plugins/ folder. Then, you should activate them also per project, per user or whatever the plugin specifically applies to.<br><span class="important">Be careful because some groups/users can be using the plugin. Deactivating it will remove the plugin from all users/groups.<br>Be EXTRA careful running the SQL init script when a plugin has been deactivated prior use (and you want to re-activate) because some scripts have DROP TABLE statements</span><br><br>');
$title_arr = array( _('Plugin Name'),
				_('Status'),
				_('Action'),
				_('Run Init Script?'),
				_('Users Using it'),
				_('Groups Using it'),);
echo $HTML->listTableTop($title_arr);

//get the directories from the plugins dir

$handle = opendir($sys_plugins_path);
$j = 0;

while ($filename = readdir($handle)) {
	//Don't add special directories '..' or '.' to the list
	$status=0; 
	if (($filename!='..') && ($filename!='.') && ($filename!="CVS") && is_dir($sys_plugins_path.'/'.$filename)) {
		//check if the plugin is in the plugins table
		$sql = "SELECT plugin_name FROM plugins WHERE plugin_name = '$filename'"; // see if the plugin is there
		$res = db_query($sql);
		if (!$res) {
			exit_error("SQL ERROR",db_error());
		}
		if (db_numrows($res)!=0) {
			$msg = _('Active');
			$status="active";
			
			$link = "<a href=\"javascript:change('" . getStringFromServer('PHP_SELF') . "?update=$filename&action=deactivate";
			$sql = "SELECT  u.user_name FROM plugins p, user_plugin up, users u WHERE p.plugin_name = '$filename' and up.user_id = u.user_id and p.plugin_id = up.plugin_id";
			$res = db_query($sql);
			if (db_numrows($res)>0) {
				// tell the form to delete the users, so that we don�t re-do the query
				$link .= "&delusers=1";
				$users = " ";
				for($i=0;$i<db_numrows($res);$i++) {
					$users .= db_result($res,$i,0) . " | ";
				}
				$users = substr($users,0,strlen($users) - 3); //remove the last |
			} else {
				$users = "none";
			}
			$sql = "SELECT g.group_name FROM plugins p, group_plugin gp, groups g WHERE plugin_name = '$filename' and gp.group_id = g.group_id and p.plugin_id = gp.plugin_id";
			$res = db_query($sql);
			if (db_numrows($res)>0) {
				// tell the form to delete the groups, so that we don�t re-do the query
				$link .= "&delgroups=1";
				$groups = " ";
				for($i=0;$i<db_numrows($res);$i++) {
					$groups .= db_result($res,$i,0) . " | ";
				}
				$groups = substr($groups,0,strlen($groups) - 3); //remove the last |
			} else {
				$groups = "none";
			}
			$link .= "','$j');" . '">' . _('Deactivate') . "</a>";
			$init = '<input id="'.$j.'" type="checkbox" disabled name="script[]" value="'.$filename.'">';
		} else {
			$msg = _('Inactive');
			$status = "inactive";
			$link = "<a href=\"javascript:change('" . getStringFromServer('PHP_SELF') . "?update=$filename&action=activate','$j');" . '">' . _('Activate') . "</a>";
			$init = '<input id="'.$j.'" type="checkbox" name="script[]" value="'.$filename.'">';
			$users = "none";
			$groups = "none";
		}

		echo '<tr '. $HTML->boxGetAltRowStyle($j+1) .'>'.
		 	'<td>'. $filename.'</td>'.
		 	'<td span class="'.$status.'">'. $msg .'</span></td>'.
		 	'<td><div align="center">'. $link .'</div></td>'.
		 	'<td><div align="center">'. $init .'</div></td>'.
		 	'<td><div align="left">'. $users .'</div></td>'.
		 	'<td><div align="left">'. $groups .'</div></td></tr>';

		$j++;
	}
}

echo $HTML->listTableBottom();

?>

</form>

<?php


site_admin_footer(array());

?>
