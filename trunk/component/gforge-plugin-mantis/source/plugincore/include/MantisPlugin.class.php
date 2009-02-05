<?php
/*
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
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
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once ("common/novaforge/log.php");
require_once ("plugins/mantis/include/mantisfunctions.php");
require_once ("plugins/mantis/include/gforgefunctions.php");

// Path of the reverse-proxy script
$g_ReverseProxyPath = "/plugins/mantis/proxy";

class MantisPlugin extends Plugin
{

	function MantisPlugin ()
	{
		$this->Plugin () ;
		$this->name = "mantis";
		$this->text = "Mantis";
		$this->hooks [] = "groupmenu";
		$this->hooks [] = "groupisactivecheckbox";
		$this->hooks [] = "groupisactivecheckboxpost";
		$this->hooks [] = "project_admin_plugins";
		$this->hooks [] = "site_admin_option_hook";
		$this->hooks [] = "session_before_login";
		$this->hooks [] = "before_logout_redirect";
		$this->hooks [] = "mypage";
		$this->hooks [] = "fill_cron_arr";
	}

	function CallHook ($hookname, $params)
	{
		global
			$Language,
			$HTML,
			$G_SESSION;

		switch ($hookname)
		{
			case "groupmenu" :
				$group = &group_get_object ($params ["group"]);
				if ((isset ($group) !== false)
				    &&  (is_object ($group) == true)
				    &&  ($group->isError () == false)
				    &&  ($group->isProject () == true))
				{
					if ($group->usesPlugin ($this->name) == true)
					{
						$params ['DIRS'] [] = "/plugins/mantis/proxy/?group_id=" . $group->getID ();
						$params ['TITLES'] [] = dgettext ("gforge-plugin-mantis", "tab_title");
					}
					if ($params ["toptab"] == $this->name)
					{
						$params ["selected"] = count ($params ["TITLES"]) - 1;
					}
				}
				break;
			case "groupisactivecheckbox" :
				$group = &group_get_object ($params ["group"]);
				echo "<tr><td><input type=\"checkbox\" name=\"use_mantis\" value=\"1\"";
				if ($group->usesPlugin ($this->name) == true)
				{
					echo " checked";
				}
				echo "></td><td><strong>". dgettext ("gforge-plugin-mantis", "use_mantis") ."</strong></td></tr>\n";
				break;
			case "groupisactivecheckboxpost" :
				$group = &group_get_object ($params ["group"]);
				if (getIntFromRequest ("use_mantis") == 1)
				{
					$group->setPluginUse ($this->name, true);
				}
				else
				{
					$group->setPluginUse ($this->name, false);
				}
				break;
			case "project_admin_plugins" :
				$group = &group_get_object ($params ["group_id"]);
				if ((isset ($group) !== false)
				    &&  (is_object ($group) == true)
				    &&  ($group->isError () == false)
				    &&  ($group->isProject () == true)
				    &&  ($group->usesPlugin ($this->name) == true))
				{
					echo "<a href=\"/plugins/mantis/admin.php?group_id=" . $group->getID () . "\">" . dgettext ("gforge-plugin-mantis", "title_admin") . "</a><br/>";
				}
				break;
			case "site_admin_option_hook" :
				echo "<li><a href=\"/plugins/mantis/siteAdmin.php\">" . dgettext ("gforge-plugin-mantis", "title_site_admin") . "</a><br/></li>";
				break;
			case "session_before_login" :
			case "before_logout_redirect" :
				setcookie ("MANTIS_PROJECT_COOKIE", "", time () - 3600, "/");
				setcookie ("MANTIS_STRING_COOKIE", "", time () - 3600, "/");
				setcookie ("MANTIS_VIEW_ALL_COOKIEE", "", time () - 3600, "/");
				break;
			case "mypage" :
				if ((isset ($G_SESSION) == true) &&  (is_object ($G_SESSION) == true))
				{
					$query = "SELECT g.group_name, p.name, p.mantis_id, p.url,p.project_id "
					       . "FROM groups g ,user_group u,plugin_mantis_project p "
					       . "WHERE g.group_id=u.group_id AND g.group_id=p.gforge_id "
					       . "AND u.user_id=" . $G_SESSION->getId () ." AND g.status='A' "
					       . "ORDER BY group_name";
					$result = db_query ($query);
					if ($result !== false)
					{
						$numrows = db_numrows ($result);
						if ($numrows > 0)
						{
							$array_group_names = array ();
							$array_mantis_names = array();
							$array_mantis_ids = array();
							$array_mantis_urls = array();
							$array_mantis_project_ids = array();
							for ($index = 0; $index < $numrows; $index++)
							{
								$array_group_names [] = db_result ($result, $index, 0);
								$array_mantis_names [] = db_result ($result, $index, 1);
								$array_mantis_ids [] = db_result ($result, $index, 2);
								$array_mantis_urls [] = db_result ($result, $index, 3);
								$array_mantis_project_ids [] = db_result ($result, $index, 4);
							}
						}
					}
					else
					{
						log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
					}
				}
				if (isset ($array_group_names) == true)
				{
					if (count ($array_group_names) > 0)
					{
						echo $HTML->boxMiddle (dgettext ("gforge-plugin-mantis", "affected_mantis_items"), false, false);
						$array_display_names = array();
						$array_bug_ids = array ();
						$array_bug_summaries = array ();
						$array_bug_urls = array ();
						$error = "";
						$index = 0;
						while (($index < count ($array_mantis_urls)) && ($error == ""))
						{
							if (getMantisBugs ($array_mantis_urls [$index],
							                   $array_mantis_ids [$index],
							                   $G_SESSION->getUnixName (),
							                   $array_bug_ids_tmp,
							                   $array_bug_summaries_tmp) == true)
							{
								if (count ($array_bug_ids_tmp) > 0)
								{
									for ($j = 0; $j < count ($array_bug_ids_tmp); $j++)
									{
										$array_display_names [] = $array_group_names [$index] . " - ". $array_mantis_names [$index];
										$array_bug_urls [] = "/plugins/mantis/proxy/" . $array_mantis_project_ids [$index] . "/view.php?id=" . $array_bug_ids_tmp [$j];
										$array_bug_ids [] = $array_bug_ids_tmp [$j];
										$array_bug_summaries [] =  $array_bug_summaries_tmp [$j];
									}
								}
							}
							else
							{
								unset ($array_display_names);
								unset ($array_bug_ids);
								unset ($array_bug_urls);
								unset ($array_bug_summaries);
								$error = dgettext ("gforge-plugin-mantis", "database_error");
							}
							$index ++;
						}
						if ( $error == "" )
						{
							if (count ($array_display_names) > 0)
							{
								$last_id = "";
								for ($index = 0; $index < count ($array_display_names); $index++)
								{
									if ($array_display_names [$index] != $last_id)
									{
										echo "<tr><td colspan=\"2\"><strong>" . $array_display_names [$index]  . "</strong></td></tr>\n";
									}
									echo "<tr>";
									echo "<td width=\"10%\">" . $array_bug_ids [$index] . "</td>";
									echo "<td width=\"90%\"><a href=\"" . $array_bug_urls [$index] . "\">" . $array_bug_summaries [$index] . "</a></td>";
									echo "</tr>\n";
									$last_id = $array_display_names [$index];
								}
							}
							else
							{
								echo "<strong>" . dgettext ("gforge-plugin-mantis", "no_mantis_items_assigned") . "</strong>";
							}

						}
						else
						{
							echo "<strong>" . $error . "</strong>";
						}

					}
				}
				break;
			case "fill_cron_arr" :
				$params ["cron_arr"] [23] = "mantis_synchronize.php";
				break;
		}
	}
}

?>
