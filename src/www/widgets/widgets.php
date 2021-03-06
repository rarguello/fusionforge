<?php
/**
 *
 * Copyright 2011-2014, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/preplugins.php';
require_once $gfcommon.'include/plugins_utils.php';
require_once $gfcommon.'widget/WidgetLayoutManager.class.php';
require_once $gfcommon.'widget/Valid_Widget.class.php';

html_use_jquery();
use_javascript('/widgets/scripts/LayoutController.js');

$hp = Codendi_HTMLPurifier::instance();
if (isLogged()) {

	$request =& HTTPRequest::instance();
	$lm = new WidgetLayoutManager();
	$vLayoutId = new Valid_UInt('layout_id');
	$vLayoutId->required();
	if ($request->valid($vLayoutId)) {
		$layout_id = $request->get('layout_id');

		$vOwner = new Valid_Widget_Owner('owner');
		$vOwner->required();
		if ($request->valid($vOwner)) {
			$owner = $request->get('owner');
			$owner_id   = (int)substr($owner, 1);
			$owner_type = substr($owner, 0, 1);
			switch($owner_type) {
				case WidgetLayoutManager::OWNER_TYPE_USER:
					$owner_id = user_getid();
					$userm = UserManager::instance();
					$current = $userm->getCurrentUser();
					site_user_header(array('title'=>sprintf(_('Personal Page for %s'), user_getname())));
					$lm->displayAvailableWidgets(user_getid(), WidgetLayoutManager::OWNER_TYPE_USER, $layout_id);
					site_footer();
					break;
				case WidgetLayoutManager::OWNER_TYPE_GROUP:
					$pm = ProjectManager::instance();
					if ($project = $pm->getProject($owner_id)) {
						$group_id = $owner_id;
						$_REQUEST['group_id'] = $_GET['group_id'] = $group_id;
						$request->params['group_id'] = $group_id; //bad!
						if (forge_check_perm('project_admin', $group_id) ||
							forge_check_global_perm('forge_admin')) {
							if (HTTPRequest::instance()->get('update') == 'layout') {
								$title = _("Customize Layout");
							} else {
								$title = _("Add widgets");
							}
							site_project_header(array('title'=>$title, 'group'=>$group_id, 'toptab'=>'summary'));
							$lm->displayAvailableWidgets($group_id, WidgetLayoutManager::OWNER_TYPE_GROUP, $layout_id);
							site_footer();
						} else {
							$GLOBALS['Response']->redirect('/projects/'.$project->getUnixName().'/');
						}
					}
					break;
				default:
					break;
			}
		}
	}
} else {
	exit_not_logged_in();
}
