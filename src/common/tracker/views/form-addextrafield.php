<?php
/**
 * Tracker Facility
 *
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2014, Franck Villaume - TrivialDev
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

global $HTML;

//
//  FORM TO BUILD SELECTION BOXES
//

$title = sprintf(_('Manage Custom Fields for %s'), $ath->getName());
$ath->adminHeader(array('title'=>$title));

/*
	List of possible user built Selection Boxes for an ArtifactType
*/
$efarr = $ath->getExtraFields();
$eftypes=ArtifactExtraField::getAvailableTypes();
$keys=array_keys($efarr);
$rows=count($keys);
if ($rows > 0) {

	$title_arr=array();
	$title_arr[]=_('Custom Fields Defined');
	$title_arr[]=_('Type');
	$title_arr[]=_('Elements Defined');
	$title_arr[]=_('Add Options');
	echo $HTML->listTableTop($title_arr);

	for ($k=0; $k < $rows; $k++) {
		$i=$keys[$k];
		$id=str_replace('@','',$efarr[$i]['alias']);
		echo '<tr id="field-'.$id.'" '. $HTML->boxGetAltRowStyle($i) .">\n".
			'<td>'.$efarr[$i]['field_name'].(($efarr[$i]['is_required']) ? utils_requiredField() : '').'<a href="'.getStringFromServer('PHP_SELF').'?update_box=1&amp;id='.
				$efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
				' ['._('Edit').']</a>'.
			'<a href="'.getStringFromServer('PHP_SELF').'?deleteextrafield=1&amp;id='.
						$efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
						' ['._('Delete').']</a>'.
			'<a href="'.getStringFromServer('PHP_SELF').'?copy_opt=1&amp;id='.
						$efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
						' ['._('Copy').']</a>'.
			"</td>\n";
		echo '<td>'.$eftypes[$efarr[$i]['field_type']]."</td>\n";
		/*
			List of possible options for a user built Selection Box
		*/
		$elearray = $ath->getExtraFieldElements($efarr[$i]['extra_field_id']);

		if (!empty($elearray)) {
			$optrows=count($elearray);

			echo '<td>';
			for ($j=0; $j <$optrows; $j++) {
				echo $elearray[$j]['element_name'];
				echo ' <a href="'.getStringFromServer('PHP_SELF').'?update_opt=1&amp;id='.
				$elearray[$j]['element_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;boxid='.
				$efarr[$i]['extra_field_id'].'">'.
				'['._('Edit').']</a>';
				echo ' <a href="'.getStringFromServer('PHP_SELF').'?delete_opt=1&amp;id='.
				$elearray[$j]['element_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;boxid='.
				$efarr[$i]['extra_field_id'].'">'.
				'['._('Delete').']</a>';
				echo '<br />';
			}
		} else {
			echo '<td>';
		}

		echo "</td>\n";
		echo "<td>\n";
		if ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_SELECT
			|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_RADIO
			|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX
			|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT
			|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
			echo '<a href="'.getStringFromServer('PHP_SELF').'?add_opt=1&amp;boxid='.
				$efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">['.
				_('Add/Reorder choices').']</a>';
		}
		echo "</td>\n";
		echo "</tr>\n";
	}
	echo $HTML->listTableBottom();
	echo $HTML->addRequiredFieldsInfoBox();
} else {
	echo $HTML->warning_msg(_('You have not defined any custom fields'));
}

echo "<h2>"._('Add New Custom Field')."</h2>";
?>
<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
<p>
<input type="hidden" name="add_extrafield" value="y" />
<strong><?php echo _('Custom Field Name').utils_requiredField()._(':'); ?></strong><br />
<input type="text" name="name" value="" size="15" maxlength="30" required="required" />
</p>
<p>
<strong><?php echo _('Field alias')._(':'); ?></strong><br />
<input type="text" name="alias" value="" size="15" maxlength="30" />
</p>

<p>
<strong><?php  echo _('Type of custom field')._(':'); ?></strong><br />
<input type="radio" name="field_type" value="1" required="required" /> <?php echo _('Select Box'); ?><br />
<input type="radio" name="field_type" value="2" /> <?php echo _('Check Box'); ?><br />
<input type="radio" name="field_type" value="3" /> <?php echo _('Radio Buttons'); ?><br />
<input type="radio" name="field_type" value="4" /> <?php echo _('Text Field'); ?><br />
<input type="radio" name="field_type" value="5" /> <?php echo _('Multi-Select Box'); ?><br />
<input type="radio" name="field_type" value="6" /> <?php echo _('Text Area'); ?><br />
<?php if (!$ath->usesCustomStatuses()) { ?>
<input type="radio" name="field_type" value="7" /> <?php echo _('Status'); ?><br />
<?php } ?>
<!--<input type="radio" name="field_type" value="8" /> <?php echo _('Box type technician'); ?><br />-->
<input type="radio" name="field_type" value="9" /> <?php echo _('Relation between artifacts'); ?><br />
<p>
<?php echo _('Text Fields and Text Areas need to have Size/Maxlength and Rows/Cols defined, respectively.'); ?><br />
<?php echo _('Text Field Size/Text Area Rows'); ?>
	<input type="text" name="attribute1" value="20" size="2" maxlength="2" /><br />
<?php echo _('Text Field Maxlength/Text Area Columns'); ?>
	<input type="text" name="attribute2" value="80" size="2" maxlength="2" /><br />
<?php echo _('Hide the default none value'); ?>
	<input type="checkbox" name="hide100" /><br />
<?php echo _('Label for the none value'); ?>
	<input type="text" name="show100label" size="30" value="<?php echo _('none') ?>" /><br />
</p>
<?php
echo $HTML->warning_msg(_('Warning: this add new custom field'));
?>
<p>
<input type="submit" name="post_changes" value="<?php echo _('Add Custom Field') ?>" />
</p>
</form>
<?php

echo "<h2>"._('Custom Field Rendering Template')."</h2>";

echo "<p>";
echo '<a href="'.getStringFromServer('PHP_SELF').'?edittemplate=1&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'._('Edit template').'</a><br />';
echo '<a href="'.getStringFromServer('PHP_SELF').'?deletetemplate=1&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'._('Delete template').'</a><br />';
echo "</p>";

$ath->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
