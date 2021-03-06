<?php
/**
 * Copyright (C) 2008-2009 Alcatel-Lucent
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012, Franck Villaume - TrivialDev
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

/**
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Full List ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/trove.php';

if (!forge_get_config('use_project_full_list')) {
	exit_disabled();
}

$HTML->header(array('title'=>_('Project List'),'pagename'=>'softwaremap'));
$HTML->printSoftwareMapLinks();

$projects = get_public_active_projects_asc($TROVE_HARDQUERYLIMIT);

$querytotalcount = count($projects);

// #################################################################
// limit/offset display

$page = getIntFromRequest('page',1);

// store this as a var so it can be printed later as well
$html_limit = '';
if ($querytotalcount == $TROVE_HARDQUERYLIMIT) {
	$html_limit .= sprintf(_('More than <strong>%d</strong> projects in result set.'), $querytotalcount);
}
$html_limit .= sprintf(_('<strong>%d</strong> projects in result set.'), $querytotalcount);

$html_limit .= ' ';

// only display pages stuff if there is more to display
if ($querytotalcount > $TROVE_BROWSELIMIT) {
	$html_limit .= html_trove_limit_navigation_box($_SERVER['PHP_SELF'], $querytotalcount, $TROVE_BROWSELIMIT, $page);
}

print $html_limit."<hr />\n";

// #################################################################
// print actual project listings
for ($i_proj=0;$i_proj<$querytotalcount;$i_proj++) {
	$row_grp = $projects[$i_proj];

	// check to see if row is in page range
	if (($i_proj >= (($page-1)*$TROVE_BROWSELIMIT)) && ($i_proj < ($page*$TROVE_BROWSELIMIT))) {
		$viewthisrow = 1;
	} else {
		$viewthisrow = 0;
	}

	if ($viewthisrow) {

		// Embed RDFa description for /projects/PROJ_NAME
		$proj_uri = util_make_url_g(strtolower($row_grp['unix_group_name']),$row_grp['group_id']);
		print '<div typeof="doap:Project sioc:Space" about="'.$proj_uri.'">'."\n";
		print '<span rel="planetforge:hosted_by" resource="'. util_make_url ('/') .'"></span>'."\n";

		print '<table class="fullwidth">';
		print '<tr class="top"><td colspan="2">';
		print util_make_link_g(strtolower($row_grp['unix_group_name']),$row_grp['group_id'],'<strong>'
			.'<span property="doap:name">'
			.$row_grp['group_name']
			.'</span>'
			.'</strong>').' ';

		if ($row_grp['short_description']) {
			print "- "
			. '<span property="doap:short_desc">'
			. $row_grp['short_description']
			. '</span>';
		}

		// extra description
		print '</td></tr><tr class="top"><td>';
		// list all trove categories
		if (forge_get_config('use_trove')) {
			print trove_getcatlisting($row_grp['group_id'],0,1,1);
		}
		print '</td>';
		print '<td class="bottom align-right"><br />'._('Register Date:').' <strong>'.date(_('Y-m-d H:i'),$row_grp['register_time']).'</strong></td>';
		print '</tr>';
		print '</table>';
        print '</div>'; // /doap:Project
		print '<hr />';
	} // end if for row and range chacking
}

// print bottom navigation if there are more projects to display
if ($querytotalcount > $TROVE_BROWSELIMIT) {
	print $html_limit;
}

$HTML->footer();
