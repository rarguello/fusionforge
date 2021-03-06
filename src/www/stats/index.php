<?php
/**
 * Sitewide Statistics
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) FusionForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
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
require_once $gfwww.'stats/site_stats_utils.php';

session_require_global_perm ('forge_stats', 'read') ;

$HTML->header(array('title'=>sprintf(_('%s Sitewide Aggregate Statistics'), forge_get_config ('forge_name'))));

//
// BEGIN PAGE CONTENT CODE
//

?>

<hr />

<table class="fullwidth">
<tr class="align-center">
<td><strong><?php echo _('OVERVIEW STATS'); ?></strong></td>
<td><a href="projects.php"><?php echo _('PROJECT STATS'); ?></a></td>
<td><a href="graphs.php"><?php echo _('SITE GRAPHS'); ?></a></td>
</tr>
</table>

<hr />

<?php

stats_site_aggregate();

stats_site_projects_daily( 7 );

stats_site_projects_monthly( );

echo '<h2>'._('Other statistics').'</h2>';
echo '<ul><li><a href="i18n.php">'.("I18n Statistics").'</a></li></ul>';

//
// END PAGE CONTENT CODE
//

$HTML->footer();
