<?php
/**
 * Project Statistics Page
 *
 * Copyright 2003 GForge, LLC
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2013, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/Report.class.php';
require_once $gfcommon.'reporting/ReportProjectAct.class.php';
require_once $gfwww.'project/admin/project_admin_utils.php';


$group_id = getIntFromRequest('group_id');
if ( !$group_id ) {
	exit_no_group();
}

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
    exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(),'admin');
}

$report=new Report();
if ($report->isError()) {
    exit_error($report->getErrorMessage(),'admin');
}

$area = getStringFromRequest('area');
$SPAN = getIntFromRequest('SPAN', REPORT_TYPE_MONTHLY);
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

/*
 * Set the start date to birth of the project.
 */
$res=db_query_params('SELECT register_time FROM groups WHERE group_id=$1', array($group_id));
$report->site_start_date=db_result($res,0,'register_time');

if (!$start || !$end) $z =& $report->getMonthStartArr();

if (!$start) {
	$start = $z[0];
}
if (!$end) {
	$end = $z[count($z)-1];
}
if ($end < $start) list($start, $end) = array($end, $start);

// Find a default SPAN value depending on the number of days.
$delta=($end - $start)/24/60/60;
if (!$SPAN) {
	$SPAN = 1;
	if ($delta > 60) $SPAN=2;
	if ($delta > 365) $SPAN=3;
}

if ($SPAN && !is_numeric($SPAN)) { $SPAN = 1; }
if ($start && !is_numeric($start)) { $start = false; }
if ($end && !is_numeric($end)) { $end = false; }

html_use_jqueryjqplotpluginCanvas();
html_use_jqueryjqplotpluginhighlighter();
html_use_jqueryjqplotplugindateAxisRenderer();

project_admin_header(array('title'=>_('Project Statistics').'','group'=>$group_id));

//
// BEGIN PAGE CONTENT CODE
//
?>
<div align="center">

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<table><tr>
<td><strong><?php echo _('Areas'); ?>:</strong><br /><?php echo report_area_box('area',$area,$group); ?></td>
<td><strong><?php echo _('Type'); ?>:</strong><br /><?php echo report_span_box('SPAN',$SPAN); ?></td>
<td><strong><?php echo _('Start Date'); ?>:</strong><br /><?php echo report_months_box($report, 'start', $start); ?></td>
<td><strong><?php echo _('End Date'); ?>:</strong><br /><?php echo report_months_box($report, 'end', $end); ?></td>
<td><input type="submit" name="submit" value="<?php echo _('Refresh'); ?>" /></td>
</tr></table>
</form>
<p>
<?php
if ($start == $end) {
	echo '<p class="warning">'._('Cannot proceed the request. Start date is equal to end date.').'</p>';
} else {
	if (!projectact_graph($group_id, $area, $SPAN, $start, $end)) {
		echo '<p class="error">'._('Error during graphic computation.');
	}
}
?>
<noscript>
<img src="/reporting/projectact_graph.php?<?php echo "SPAN=$SPAN&amp;start=$start&amp;end=$end&amp;g_id=$group_id&amp;area=$area"; ?>" width="640" height="480" alt="stats graph" />
</noscript>
</p>
</div>
<?php

site_project_footer( array() );
