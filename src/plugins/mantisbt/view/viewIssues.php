<?php
/**
 * MantisBT plugin
 *
 * Copyright 2009, Fabien Dubois - Capgemini
 * Copyright 2009-2011, Franck Villaume - Capgemini
 * Copyright 2011, Franck Villaume - TrivialDev
 * Copyright 2010, Antoine Mercadal - Capgemini
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

/**
 * View All Issues
 * - for a specific group id
 * - for a specific user
 */

/*
 * @todo : remove all css and js
 */

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $type;
global $editable;
global $HTML;

global $prioritiesImg, $bugPerPage;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	switch ($type) {
		case "user": {
			$idsBugAll = array();
			foreach ($mantisbtConf['url'] as $mantisbtConfUrl) {
				$clientSOAP = new SoapClient($mantisbtConfUrl."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
				$idsBugAll = $clientSOAP->__soapCall('mc_issue_get_filtered_by_user', array("username" => $username, "password" => $password));
			}
			break;
		}
		case "group": {
			$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
			$idsBugAll = $clientSOAP->__soapCall('mc_project_get_issue_headers', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt'],  "page_number" => -1, "per_page" => -1));
			break;
		}
	}
} catch (SoapFault $soapFault) {
	echo $HTML->warning_msg(_('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring);
	$errorPage = true;
}
if (!isset($clientSOAP) && !isset($errorPage)) {
	echo $HTML->warning_msg(_('No data to retrieve'));
} elseif (!isset($errorPage) && isset($clientSOAP)) {

?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#expandable_ticket").hide();
	});
</script>

<?php
	// recuperation des bugs
	$listBug = array();

	$listStatus = $clientSOAP->__soapCall('mc_enum_status', array("username" => $username, "password" => $password));
	$pageActuelle = getIntFromRequest('page');
	if (empty($pageActuelle)) {
		$pageActuelle = 1;
	}
	// calcul pour la pagination
	$nombreBugs = count ($idsBugAll);
	$nombreDePages=ceil($nombreBugs/$bugPerPage);
	// Si la valeur de $pageActuelle (le numéro de la page) est plus grande que $nombreDePages...
	if($pageActuelle > $nombreDePages) {
		$pageActuelle = $nombreDePages;
	}
	$indexMin = ($pageActuelle - 1) * $bugPerPage;
	$indexMax = ($pageActuelle * $bugPerPage) -1;
	// construction du tableau
	$listBugAll = array();
	foreach ($idsBugAll as $defect) {
		foreach ($listStatus as $loopStatus) {
			if ($loopStatus->id == $defect->status) {
				$statusname = $loopStatus->name;
			}
		}
		$listBugAll[] = array( "id"=> $defect->id, "idPriority"=> $defect->priority,
					"category"=> $defect->category,"project" => $defect->project,
					"severityId" => $defect->severity, "statusId" => $defect->status,
					"last_updated" => $defect->last_updated, "status_name" => $statusname,
					"summary" => htmlspecialchars($defect->summary,ENT_QUOTES), "view_state" => $defect->view_state,
			);
	}

	if(count($listBugAll) > 0) {
		foreach ($listBugAll as $key => $defect) {
			if ( ($indexMin <= $key) && ($indexMax >= $key) ){
				$listBug[] = $defect;
			}
		}
	}

	// affichage page
	if (!count($listBug)) {
		echo $HTML->warning_msg(_('No tickets to display'));
	} else {
		$nbligne=0;

		$picto_haut = util_make_url('themes/gforge/images/picto_fleche_haut_marron.png');
		$picto_bas = util_make_url('themes/gforge/images/picto_fleche_bas_marron.png');
		$nbligne++;
		include 'jumpToIssue.php';
		echo '<table>';
		echo	'<tr>';
		// Priority
		echo		'<th width="2%">';
// 		echo			'<form name="filterprority" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'">';
// 		if ($bugfilter['sort'] == "priority" && $bugfilter['dir'] == "ASC") {
// 			echo			'<input type=hidden name="dir" value="DESC"/>';
// 		} elseif ($bugfilter['sort'] == "priority" && $bugfilter['dir'] == "DESC") {
// 			echo			'<input type="hidden" name="dir" value="ASC"/>';
// 		}
// 		if ( isset($bugfilter['show_status'])) {
// 			foreach ($bugfilter['show_status'] as $key => $childStatus) {
// 				echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
// 			}
// 		}
// 		if ( isset($bugfilter['project_id'])) {
// 			foreach ($bugfilter['project_id'] as $key => $childId) {
// 				echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
// 			}
// 		}
// 		echo				'<input type=hidden name="sort" value="priority" />';
// 		echo				'<a href="javascript:document.filterprority.submit();">P';
// 		if ($bugfilter['sort'] == "priority" && $bugfilter['dir'] == "ASC" ) {
// 			echo				'<img src="'.$picto_haut.'">';
// 		} elseif ($bugfilter['sort'] == "priority" && $bugfilter['dir'] == "DESC" ) {
// 			echo				'<img src="'.$picto_bas.'">';
// 		}
// 		echo 				'</a>';
// 		echo			'</form>';
		echo 'P';
		echo 		'</th>';
		// ID
		echo		'<th width="3%">';
// 		echo			'<form name="filterid" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'">';
// 		if ($bugfilter['sort'] == "id" && $bugfilter['dir'] == "ASC") {
// 			echo			'<input type=hidden name="dir" value="DESC"/>';
// 		} elseif ($bugfilter['sort'] == "id" && $bugfilter['dir'] == "DESC") {
// 			echo			'<input type="hidden" name="dir" value="ASC"/>';
// 		}
// 		if ( isset($bugfilter['show_status'])) {
// 			foreach ($bugfilter['show_status'] as $key => $childStatus) {
// 				echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
// 			}
// 		}
// 		if ( isset($bugfilter['project_id'])) {
// 			foreach ($bugfilter['project_id'] as $key => $childId) {
// 				echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
// 			}
// 		}
// 		echo				'<input type=hidden name="sort" value="id" />';
// 		echo				'<a href="javascript:document.filterid.submit();">ID';
// 		if ($bugfilter['sort'] == "id" && $bugfilter['dir'] == "ASC" ) {
// 			echo				'<img src="'.$picto_haut.'">';
// 		}else if ($bugfilter['sort'] == "id" && $bugfilter['dir'] == "DESC" ) {
// 			echo				'<img src="'.$picto_bas.'">';
// 		}
// 		echo 				'</a>';
// 		echo			'</form>';
		echo 'ID';
		echo 		'</th>';
		// Catégorie
		echo		'<th width="7%">';
// 		echo			'<form name="filtercat" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'">';
// 		if ($bugfilter['sort'] == "category_id" && $bugfilter['dir'] == "ASC") {
// 			echo			'<input type=hidden name="dir" value="DESC"/>';
// 		}else if ($bugfilter['sort'] == "category_id" && $bugfilter['dir'] == "DESC") {
// 			echo			'<input type="hidden" name="dir" value="ASC"/>';
// 		}
// 		if ( isset($bugfilter['show_status'])) {
// 			foreach ($bugfilter['show_status'] as $key => $childStatus) {
// 				echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
// 			}
// 		}
// 		if ( isset($bugfilter['project_id'])) {
// 			foreach ($bugfilter['project_id'] as $key => $childId) {
// 				echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
// 			}
// 		}
// 		echo				'<input type=hidden name="sort" value="category_id" />';
// 		echo				'<a href="javascript:document.filtercat.submit();">'._('Category');
// 		if ($bugfilter['sort'] == "category_id" && $bugfilter['dir'] == "ASC" ) {
// 			echo				'<img src="'.$picto_haut.'">';
// 		}else if ($bugfilter['sort'] == "category_id" && $bugfilter['dir'] == "DESC" ) {
// 			echo				'<img src="'.$picto_bas.'">';
// 		}
// 		echo 				'</a>';
// 		echo			'</form>';
		echo _('Category');
		echo 		'</th>';
		// Projet
		echo 		'<th width="7%">';
// 		echo			'<form name="projectid" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'">';
// 		if ($bugfilter['sort'] == "project_id" && $bugfilter['dir'] == "ASC") {
// 			echo			'<input type=hidden name="dir" value="DESC"/>';
// 		}else if ($bugfilter['sort'] == "project_id" && $bugfilter['dir'] == "DESC") {
// 			echo			'<input type="hidden" name="dir" value="ASC"/>';
// 		}
// 		if ( isset($bugfilter['show_status'])) {
// 			foreach ($bugfilter['show_status'] as $key => $childStatus) {
// 				echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
// 			}
// 		}
// 		if ( isset($bugfilter['project_id'])) {
// 			foreach ($bugfilter['project_id'] as $key => $childId) {
// 				echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
// 			}
// 		}
// 		echo				'<input type=hidden name="sort" value="project_id" />';
// 		echo				'<a href="javascript:document.projectid.submit();">'._('Project');
// 		if ($bugfilter['sort'] == "project_id" && $bugfilter['dir'] == "ASC" ) {
// 			echo				'<img src="'.$picto_haut.'">';
// 		}else if ($bugfilter['sort'] == "project_id" && $bugfilter['dir'] == "DESC" ) {
// 			echo				'<img src="'.$picto_bas.'">';
// 		}
// 		echo 				'</a>';
// 		echo			'</form>';
		echo _('Project');
		echo 		'</th>';
		// Sévérité
		echo 		'<th width="7%">';
// 		echo			'<form name="severity" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'">';
// 		if ($bugfilter['sort'] == "severity" && $bugfilter['dir'] == "ASC") {
// 			echo			'<input type=hidden name="dir" value="DESC"/>';
// 		}else if ($bugfilter['sort'] == "severity" && $bugfilter['dir'] == "DESC") {
// 			echo			'<input type="hidden" name="dir" value="ASC"/>';
// 		}
// 		if ( isset($bugfilter['show_status'])) {
// 			foreach ($bugfilter['show_status'] as $key => $childStatus) {
// 				echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
// 			}
// 		}
// 		if ( isset($bugfilter['project_id'])) {
// 			foreach ($bugfilter['project_id'] as $key => $childId) {
// 				echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
// 			}
// 		}
// 		echo				'<input type=hidden name="sort" value="severity" />';
// 		echo				'<a href="javascript:document.severity.submit();">'._('Severity');
// 		if ($bugfilter['sort'] == "severity" && $bugfilter['dir'] == "ASC" ) {
// 			echo				'<img src="'.$picto_haut.'">';
// 		}else if ($bugfilter['sort'] == "severity" && $bugfilter['dir'] == "DESC" ) {
// 			echo				'<img src="'.$picto_bas.'">';
// 		}
// 		echo 				'</a>';
// 		echo			'</form>';
		echo _('Severity');
		echo 		'</th>';
		// Etat
		echo 		'<th width="15%">';
// 		echo			'<form name="statusid" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'">';
// 		if ($bugfilter['sort'] == "status" && $bugfilter['dir'] == "ASC") {
// 			echo			'<input type=hidden name="dir" value="DESC"/>';
// 		}else if ($bugfilter['sort'] == "status" && $bugfilter['dir'] == "DESC") {
// 			echo			'<input type="hidden" name="dir" value="ASC"/>';
// 		}
// 		if ( isset($bugfilter['show_status'])) {
// 			foreach ($bugfilter['show_status'] as $key => $childStatus) {
// 				echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
// 			}
// 		}
// 		if ( isset($bugfilter['project_id'])) {
// 			foreach ($bugfilter['project_id'] as $key => $childId) {
// 				echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
// 			}
// 		}
// 		echo				'<input type=hidden name="sort" value="status" />';
// 		echo				'<a href="javascript:document.statusid.submit();">'._('Status');
// 		if ($bugfilter['sort'] == "status" && $bugfilter['dir'] == "ASC" ) {
// 			echo				'<img src="'.$picto_haut.'">';
// 		}else if ($bugfilter['sort'] == "status" && $bugfilter['dir'] == "DESC" ) {
// 			echo				'<img src="'.$picto_bas.'">';
// 		}
// 		echo 				'</a>';
// 		echo			'</form>';
		echo _('Status');
		echo 		'</th>';
		// Mis a jour (date)
		echo 		'<th width="7%">';
// 		echo			'<form name="lastupdate" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'">';
// 		if ($bugfilter['sort'] == "last_updated" && $bugfilter['dir'] == "ASC") {
// 			echo			'<input type=hidden name="dir" value="DESC"/>';
// 		}else if ($bugfilter['sort'] == "last_updated" && $bugfilter['dir'] == "DESC") {
// 			echo			'<input type="hidden" name="dir" value="ASC"/>';
// 		}
// 		if ( isset($bugfilter['show_status'])) {
// 			foreach ($bugfilter['show_status'] as $key => $childStatus) {
// 				echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
// 			}
// 		}
// 		if ( isset($bugfilter['project_id'])) {
// 			foreach ($bugfilter['project_id'] as $key => $childId) {
// 				echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
// 			}
// 		}
// 		echo				'<input type=hidden name="sort" value="last_updated" />';
// 		echo				'<a href="javascript:document.lastupdate.submit();">'._('Last update');
// 		if ($bugfilter['sort'] == "last_updated" && $bugfilter['dir'] == "ASC" ) {
// 			echo				'<img src="'.$picto_haut.'">';
// 		}else if ($bugfilter['sort'] == "last_updated" && $bugfilter['dir'] == "DESC" ) {
// 			echo				'<img src="'.$picto_bas.'">';
// 		}
// 		echo 				'</a>';
// 		echo			'</form>';
		echo _('Last update');
		echo 		'</th>';
		// résumé
		echo 		'<th width="29%">';
// 		echo			'<form name="summary" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'">';
// 		if ($bugfilter['sort'] == "summary" && $bugfilter['dir'] == "ASC") {
// 			echo			'<input type=hidden name="dir" value="DESC"/>';
// 		}else if ($bugfilter['sort'] == "summary" && $bugfilter['dir'] == "DESC") {
// 			echo			'<input type="hidden" name="dir" value="ASC"/>';
// 		}
// 		if ( isset($bugfilter['show_status'])) {
// 			foreach ($bugfilter['show_status'] as $key => $childStatus) {
// 				echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
// 			}
// 		}
// 		if ( isset($bugfilter['project_id'])) {
// 			foreach ($bugfilter['project_id'] as $key => $childId) {
// 				echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
// 			}
// 		}
// 		echo				'<input type=hidden name="sort" value="summary" />';
// 		echo				'<a href="javascript:document.summary.submit();">'._('Summary');
// 		if ($bugfilter['sort'] == "summary" && $bugfilter['dir'] == "ASC" ) {
// 			echo				'<img src="'.$picto_haut.'">';
// 		}else if ($bugfilter['sort'] == "summary" && $bugfilter['dir'] == "DESC" ) {
// 			echo				'<img src="'.$picto_bas.'">';
// 		}
// 		echo 				'</a>';
// 		echo			'</form>';
		echo _('Summary');
		echo 		'</th>';
/* currently informations are missing in header
		// version de détection
		echo 		'<th width="6%">';
		echo			'<form name="version" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'">';
		if ($bugfilter['sort'] == "version" && $bugfilter['dir'] == "ASC") {
			echo			'<input type=hidden name="dir" value="DESC"/>';
		}else if ($bugfilter['sort'] == "version" && $bugfilter['dir'] == "DESC") {
			echo			'<input type="hidden" name="dir" value="ASC"/>';
		}
		if ( isset($bugfilter['show_status'])) {
			foreach ($bugfilter['show_status'] as $key => $childStatus) {
				echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
			}
		}
		if ( isset($bugfilter['project_id'])) {
			foreach ($bugfilter['project_id'] as $key => $childId) {
				echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
			}
		}
		echo				'<input type=hidden name="sort" value="version" />';
		echo				'<a href="javascript:document.version.submit();">'._('Detected in');
		if ($bugfilter['sort'] == "version" && $bugfilter['dir'] == "ASC" ){
			echo				'<img src="'.$picto_haut.'">';
		}else if ($bugfilter['sort'] == "version" && $bugfilter['dir'] == "DESC" ) {
			echo				'<img src="'.$picto_bas.'">';
		}
		echo 				'</a>';
		echo			'</form>';
		echo 		'</th>';
		// corrigé en version
		echo		'<th width="6%">';
		echo			'<form name="fixed" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'">';
		if ($bugfilter['sort'] == "fixed_in_version" && $bugfilter['dir'] == "ASC") {
			echo			'<input type=hidden name="dir" value="DESC"/>';
		}else if ($bugfilter['sort'] == "fixed_in_version" && $bugfilter['dir'] == "DESC") {
			echo			'<input type="hidden" name="dir" value="ASC"/>';
		}
		if ( isset($bugfilter['show_status'])) {
			foreach ($bugfilter['show_status'] as $key => $childStatus) {
				echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
			}
		}
		if ( isset($bugfilter['project_id'])) {
			foreach ($bugfilter['project_id'] as $key => $childId) {
				echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
			}
		}
		echo				'<input type=hidden name="sort" value="fixed_in_version" />';
		echo				'<a href="javascript:document.fixed.submit();">'._('Fixed in');
		if ($bugfilter['sort'] == "fixed_in_version" && $bugfilter['dir'] == "ASC" ){
			echo				'<img src="'.$picto_haut.'">';
		}else if ($bugfilter['sort'] == "fixed_in_version" && $bugfilter['dir'] == "DESC" ) {
			echo				'<img src="'.$picto_bas.'">';
		}
		echo 				'</a>';
		echo			'</form>';
		echo 		'</th>';
		// version cible : Milestone
		echo		'<th width="6%">';
		echo			'<form name="target" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'">';
		if ($bugfilter['sort'] == "target_version" && $bugfilter['dir'] == "ASC") {
			echo			'<input type=hidden name="dir" value="DESC"/>';
		}else if ($bugfilter['sort'] == "target_version" && $bugfilter['dir'] == "DESC") {
			echo			'<input type="hidden" name="dir" value="ASC"/>';
		}
		if ( isset($bugfilter['show_status'])) {
			foreach ($bugfilter['show_status'] as $key => $childStatus) {
				echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
			}
		}
		if ( isset($bugfilter['project_id'])) {
			foreach ($bugfilter['project_id'] as $key => $childId) {
				echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
			}
		}
		echo				'<input type=hidden name="sort" value="target_version" />';
		echo				'<a href="javascript:document.target.submit();">'._('Target');
		if ($bugfilter['sort'] == "target_version" && $bugfilter['dir'] == "ASC" ){
			echo				'<img src="'.$picto_haut.'">';
		}else if ($bugfilter['sort'] == "target_version" && $bugfilter['dir'] == "DESC" ) {
			echo				'<img src="'.$picto_bas.'">';
		}
		echo 				'</a>';
		echo			'</form>';
		echo 		'</th>';
*/
		echo	'</tr>';
		$cpt = 0;
		$format = "%07d";
		foreach($listBug as $key => $bug) {
			$nbligne++;
			echo '<tr '.$HTML->boxGetAltRowStyle($nbligne).'">';
			if($prioritiesImg[$bug['idPriority']] != "") {
				echo		'<td><img src="./img/'.$prioritiesImg[$bug['idPriority']].'"></td>';
			}else{
				echo		'<td></td>';
			}
			echo		'<td><a href="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$bug['id'].'&view=viewIssue">'.sprintf($format,$bug['id']).'</a></td>';
			echo 		'<td>'.$bug['category'].'</td>';
			echo 		'<td>'.$bug['project'].'</td>';
			echo 		'<td>';
			if($bug['severityId'] > 50) {
				echo		'<b>';
			}
			echo			$bug['severityId'];
			if($bug['severityId'] > 50) {
				echo		'</b>';
			}
			echo		'</td>';
			echo 		'<td>'.$bug['status_name'].'</td>';
			echo 		'<td>'.strftime("%d/%m/%Y",strtotime($bug['last_updated'])).'</td>';
			echo 		'<td>'.$bug['summary'];
			if ($bug['view_state'] == 50) {
				echo '<img src="./img/protected.gif">';
			}
			echo 		'</td>';
/*
			echo 		'<td>'.$bug['version'].'</td>';
			echo 		'<td>'.$bug['fixed_in_version'].'</td>';
			echo 		'<td>'.$bug['target_version'].'</td>';
*/
			echo	'</tr>';
			$cpt ++;
		}
		echo "</table><br/>";
	}

	// Add new issue
	if ($type == "group" && $editable) {
	?>
		<p class="notice_title" onclick='jQuery("#expandable_ticket").slideToggle(300);'><?php echo _('Add a new ticket') ?></p>
		<div id='expandable_ticket' class="notice_content">
		<?php include("addIssue.php") ?>
		</div>
		<br/>
	<?php
	}

	// Creation de la pagination
	echo '<div align="center">';
	for($i=1; $i<=$nombreDePages; $i++)
	{
		if($i==$pageActuelle) //Si il s'agit de la page actuelle...
		{
			echo '| <b>'.$i.'</b>';
		} else {
			echo '<form style="display:inline" name="page'.$i.'" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&page='.$i.'" >';
			echo 	'<input type="hidden" name="sort" value="'.$bugfilter['sort'].'" />';
			echo 	'<input type="hidden" name="dir" value="'.$bugfilter['dir'].'" />';
			if ( isset($bugfilter['show_status'])) {
				foreach ($bugfilter['show_status'] as $key => $childStatus) {
					echo	'<input type="hidden" name="projectStatus[]" value="'.$childStatus.'"/>';
				}
			}
			if ( isset($bugfilter['project_id'])) {
				foreach ($bugfilter['project_id'] as $key => $childId) {
					echo	'<input type="hidden" name="projectChildId[]" value="'.$childId.'"/>';
				}
			}
			echo '| <a href="javascript:document.page'.$i.'.submit();">'.$i.'</a>';
			echo '</form>';
		}
	}
	echo 	'</div>';
}
