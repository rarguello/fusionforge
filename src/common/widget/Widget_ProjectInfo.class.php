<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';

/**
 * Widget_ProjectInfo
 */
class Widget_ProjectInfo extends Widget {
	public function __construct() {
		$this->Widget('projectinfo');
	}

	public function getTitle() {
		return _('Project Information');
	}

	public function getContent() {
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$pm = ProjectManager::instance();
		$project = $pm->getProject($group_id);
		// Tag list
		if (forge_get_config('use_project_tags')) {
			$list_tag = list_project_tag($group_id);
			echo html_ao('p').html_image('ic/tag.png'). ' ';
			if ($list_tag) {
				echo _('Tags')._(': '). $list_tag;
			} else {
				$project = group_get_object($group_id);
				if (forge_check_perm ('project_admin', $project->getID())) {
					echo util_make_link('/project/admin/?group_id='.$group_id, _('No tag defined for this project'));
				}
				else {
					echo html_e('span', array(), _('No tag defined for this project'), false);
				}
			}
			echo html_ac(html_ap() - 1);
		}

		if(forge_get_config('use_trove')) {
			echo html_e('br');
			print stripslashes(trove_getcatlisting($group_id,0,1,1))."\n";
		}

		// registration date
		$project_start_date = $project->getStartDate();
		print(_('Registered')._(': ').
				'<span property="doap:created" content="'.date('Y-m-d', $project_start_date).'">'.
				date(_('Y-m-d H:i'), $project_start_date).
				"</span>\n");

		// Get the activity percentile
		// CB hide stats if desired
		if ($project->usesStats()) {
			$actv = db_query_params ('SELECT ranking FROM project_weekly_metric WHERE group_id=$1',
					array($group_id));
			if (db_numrows($actv) > 0){
				$actv_res = db_result($actv,0,"ranking");
			} else {
				$actv_res = 0;
			}
			if (!$actv_res) {
				$actv_res=0;
			}
			print '<br />'.sprintf (_('Activity Ranking: <strong>%d</strong>'), $actv_res)."\n";
			print '<br />'._('View project').' '.util_make_link('/project/stats/?group_id='.$group_id, _('Statistics'));
			if ( ($project->usesTracker() && forge_get_config('use_tracker')) || ($project->usesPm() && forge_get_config('use_pm')) ) {
				print sprintf(_(' or <a href="%s">Activity</a>'),util_make_uri('/project/report/?group_id='.$group_id))."\n";
			}
			print '<br />'.sprintf(_('View list of <a href="%s">RSS feeds</a> available for this project.'), util_make_uri('/export/rss_project.php?group_id='.$group_id)). ' ' . html_image('ic/rss.png',16,16,array());
		}

		if(forge_get_config('use_people')) {
			$jobs_res = db_query_params('SELECT name
					FROM people_job,people_job_category
					WHERE people_job.category_id=people_job_category.category_id
					AND people_job.status_id=1
					AND group_id=$1
					GROUP BY name',
					array ($group_id),
					2);
			if ($jobs_res) {
				$num=db_numrows($jobs_res);
				if ($num>0) {
					print '<p>';
					printf(
							ngettext('HELP WANTED: This project is looking for a <a href="%1$s">"%2$s"</a>.',
								'HELP WANTED: This project is looking for people to fill <a href="%1$s">several different positions</a>.',
								$num),
							util_make_uri('/people/?group_id='.$group_id),
							db_result($jobs_res,0,"name"));
					print "</p>\n";
					//print '<div rel="fusionforge:has_job" typeof="fusionforge:Job" xmlns:fusionforge="http://fusionforge.org/fusionforge#">';
					//print '<span rel="dc:title" content="'. db_result($jobs_res,0,"name").'" xmlns:dc="http://purl.org/dc/elements/1.1/">';
					//print '</span>';
					//echo '</div>';
					//end of job description part
				}
			}
		}

		$hook_params = array();
		$hook_params['group_id'] = $group_id;
		plugin_hook("project_after_description",$hook_params);
		plugin_hook('hierarchy_views', array($group_id, 'home'));
	}

	public function canBeUsedByProject(&$project) {
		return true;
	}

	function getDescription() {
		return _('Some infos about the project.');
	}
}
