<?php
/**
 * massupdate-fields.php - code to handle mass updating admin defined fields for * artifacts   
 *
 * Copyright 2004 (c) Anthony J. Pugliese
 *
 * @version   $Id$
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
$result=$ath->getSelectionBoxes();
$resultc=$ath->getArtifactChoices($ah->getID());
$rows=db_numrows($result);
$setrows=db_numrows($resultc);
$transferct=0;
$changect=0;
if($result &&$rows > 0) {
	for ($j=0; $j < $rows; $j++){
		if ($j < $setrows) {
			if ($extra_fields_choice[$j] != 100){ 
				if (db_result($resultc,$j,'choice_id') !== ($extra_fields_choice[$j])) {
					
					$ah->updateExtraFields(db_result($resultc,$j,'id'),$extra_fields_choice[$j]);
					$old=(db_result($resultc,$j,'choice_id'));
					$oldnames =$ath->getBoxOptionsName($old);
					$ah->addHistory(db_result($result,$j,'selection_box_name'),db_result($oldnames,'0','box_options_name'));
					$changect=$changect+1;
				}
			}	
			}else {
				$ah->createExtraFields($extra_fields_choice[$j]);
			if ($extra_fields_choice[$j] !=='100') {
				$transferct=$transferct+1;
			}
		}		
	}
}			
?>

