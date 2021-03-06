<?php
/**
 * ProjectImportPlugin Class
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

forge_define_config_item('storage_base','projectimport','$core/data_path/plugins/projectimport/');
forge_define_config_item('libmagic_db','projectimport','/usr/share/misc/magic.mgc');

class ProjectImportPlugin extends Plugin {
	function ProjectImportPlugin () {
		$this->Plugin() ;
		$this->name = "projectimport" ;
		$this->text = "Project import" ; // To show in the tabs, use...
		$this->hooks[] = "groupmenu" ;	// To put into the project tabs
		/*
		$this->hooks[] = "user_personal_links";//to make a link to the user's personal part of the plugin
		$this->hooks[] = "usermenu" ;
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
		$this->hooks[] = "userisactivecheckbox" ; // The "use ..." checkbox in user account
		$this->hooks[] = "userisactivecheckboxpost" ; //
		$this->hooks[] = "project_admin_plugins"; // to show up in the admin page fro group
		*/
		// The plugin has a link added to the Project administration part of site admin
		$this->hooks[] = "site_admin_project_maintenance_hook";
		$this->hooks[] = "site_admin_user_maintenance_hook";
	}

	function CallHook ($hookname, &$params) {
		global $use_projectimportplugin,$G_SESSION,$HTML;
		/*if ($hookname == "usermenu") {
			$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("projectimport")) {
				$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we're calling is the user one
				echo ' | ' . $HTML->PrintSubMenu (array ($text),
						  array ('/plugins/projectimport/index.php' . $param ));
			}
		} else */ if ($hookname == "groupmenu") {
			$group_id=$params['group'];
			$project = group_get_object($group_id);
			if (!$project || !is_object($project)) {
				return;
			}
			if ($project->isError()) {
				return;
			}
			if (!$project->isProject()) {
				return;
			}
			if ( $project->usesPlugin ( $this->name ) ) {
				$params['TITLES'][]=$this->text;
				$params['ADMIN'][] = NULL;
				$params['TOOLTIPS'][] = NULL;
				$params['DIRS'][]=util_make_url ('/plugins/projectimport/index.php?type=group&group_id=' . $group_id . "&pluginname=" . $this->name) ; // we indicate the part we're calling is the project one
			} else {
				$params['TITLES'][]=$this->text." is [Off]";
				$params['ADMIN'][] = NULL;
				$params['TOOLTIPS'][] = NULL;
				$params['DIRS'][]='';
			}
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
		} /*elseif ($hookname == "groupisactivecheckbox") {
			//Check if the group is active
			// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
			$group_id=$params['group'];
			$group = group_get_object($group_id);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_projectimportplugin" value="1" ';
			// checked or unchecked?
			if ( $group->usesPlugin ( $this->name ) ) {
				echo "checked";
			}
			echo " /><br/>";
			echo "</td>";
			echo "<td>";
			echo "<strong>Use ".$this->text." Plugin</strong>";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "groupisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group_id=$params['group'];
			$group = group_get_object($group_id);
			$use_projectimportplugin = getStringFromRequest('use_projectimportplugin');
			if ( $use_projectimportplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == "userisactivecheckbox") {
			//check if user is active
			// this code creates the checkbox in the user account manteinance page to activate/deactivate the plugin
			$user = $params['user'];
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_projectimportplugin" value="1" ';
			// checked or unchecked?
			if ( $user->usesPlugin ( $this->name ) ) {
				echo "checked";
 			}
			echo " />    Use ".$this->text." Plugin";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "userisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the user account manteinance page
			$user = $params['user'];
			$use_projectimportplugin = getStringFromRequest('use_projectimportplugin');
			if ( $use_projectimportplugin == 1 ) {
				$user->setPluginUse ( $this->name );
			} else {
				$user->setPluginUse ( $this->name, false );
			}
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_projectimportplugin" value="1" ';
			// checked or unchecked?
			if ( $user->usesPlugin ( $this->name ) ) {
				echo "checked";
			}
			echo " />    Use ".$this->text." Plugin";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "user_personal_links") {
			// this displays the link in the user's profile page to it's personal ProjectImport (if you want other sto access it, youll have to change the permissions in the index.php
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			$text = $params['text'];
			//check if the user has the plugin activated
			if ($user->usesPlugin($this->name)) {
				echo '	<p>' ;
				echo util_make_link ("/plugins/projectimport/index.php?id=$userid&type=user&pluginname=".$this->name,
						     _('View Personal ProjectImport')
					);
				echo '</p>';
			}
		} elseif ($hookname == "project_admin_plugins") {
			// this displays the link in the project admin options page to it's  ProjectImport administration
			$group_id = $params['group_id'];
			$group = group_get_object($group_id);
			if ( $group->usesPlugin ( $this->name ) ) {
				echo util_make_link ("/plugins/projectimport/index.php?id=".$group->getID().'&type=admin&pluginname='.$this->name,
						     _('View the ProjectImport Administration')).'<br />';
			}
		}
		elseif ($hookname == "blahblahblah") {
			// ...
		}
		*/
	}

	/**
	 * Displays the link in the Project Maintenance part of the Site Admin ('site_admin_project_maintenance_hook' plugin_hook_by_reference() -style hook)
	 * @param array $params for concatenating return value in ['results']
	 */
	function site_admin_project_maintenance_hook (&$params) {
		$html = $params['result'];
		$html .= '<li>'.
			util_make_link ('/plugins/'.$this->name.'/projectsimport.php',
						     _("Import projects"). ' [' . _('Project import plugin') . ']') .'</li>';
		$params['result'] = $html;
	}

	/**
	 * Displays the link in the User Maintenance part of the Site Admin ('site_admin_user_maintenance_hook' plugin_hook_by_reference() -style hook)
	 * @param array $params for concatenating return value in ['results']
	 */
	function site_admin_user_maintenance_hook (&$params) {
		$html = $params['result'];
		$html .= '<li>'.
			util_make_link ('/plugins/'.$this->name.'/usersimport.php',
						     _("Import users"). ' [' . _('Project import plugin') . ']') .'</li>';
		$params['result'] = $html;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
