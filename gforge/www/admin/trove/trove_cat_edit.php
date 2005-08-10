<?php
/**
 * Site Admin: Trove Admin: edit category
 *
 * This page is linked from trove_cat_list.php, page to browse full
 * Trove tree.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('pre.php');
require_once('www/include/trove.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

// ########################################################

if (getStringFromRequest('submit')) {
	$form_parent = getStringFromRequest('form_parent');
	$form_shortname = getStringFromRequest('form_shortname');
	$form_trove_cat_id = getIntFromRequest('form_trove_cat_id');
	$form_shortname = getStringFromRequest('form_shortname');
	$form_fullname = getStringFromRequest('form_fullname');
	$form_description = getStringFromRequest('form_description');
	$newroot = trove_getrootcat($form_parent);

	if ($form_shortname) {
		if ($form_trove_cat_id == $form_parent) {
			exit_error($Language->getText(
					   'admin_trove_cat_edit','error_tove_equal_parent'),
				   db_error()
			);
		} else {
			$res = db_query("
				UPDATE trove_cat
				SET	shortname='".htmlspecialchars($form_shortname)."',
					fullname='".htmlspecialchars($form_fullname)."',
					description='".htmlspecialchars($form_description)."',
					parent='$form_parent',
					version='".date("Ymd",time())."01',
					root_parent='$newroot'
				WHERE trove_cat_id='$form_trove_cat_id'
			");
		}

		if (!$res || db_affected_rows($res)<1) {
			exit_error(
				$Language->getText('admin_trove_cat_edit','error_in_trove_operation'),
				db_error()
			);
		}
	}
	// update full paths now
	if($newroot!=0) {
		trove_genfullpaths($newroot,trove_getfullname($newroot),$newroot);
		trove_updaterootparent($form_trove_cat_id,$newroot);
	}
	else {
		trove_genfullpaths($form_trove_cat_id,trove_getfullname($form_trove_cat_id),$form_trove_cat_id);
		trove_updaterootparent($form_trove_cat_id,$form_trove_cat_id);
	}
	db_query("update trove_group_link set trove_cat_root=(select root_parent from trove_cat where trove_cat_id=trove_group_link.trove_cat_id)");

	session_redirect("/admin/trove/trove_cat_list.php");
}

if ($GLOBALS["delete"]) {
	if ($form_trove_cat_id==$default_trove_cat){
		exit_error( $Language->getText('admin_trove_cat_edit','error_in_trove_operation_cant_delete'));
	}
	$res=db_query("SELECT * FROM trove_cat WHERE parent='$form_trove_cat_id'");
	if (!$res) {
		exit_error( $Language->getText('admin_trove_cat_edit','error_in_trove_operation'), db_error());
	}
	if (db_numrows($res)>0) {
		exit_error( $Language->getText('admin_trove_cat_edit','cant_delete_has_subcategories'), db_error());
	} else {
		$res=db_query("DELETE FROM trove_treesums WHERE trove_cat_id='$form_trove_cat_id'");
		if (!$res) {
			exit_error( $Language->getText('admin_trove_cat_edit','error_in_trove_operation'), db_error());
		}
		$res=db_query("DELETE FROM trove_group_link WHERE trove_cat_id='$form_trove_cat_id'");
		if (!$res) {
			exit_error( $Language->getText('admin_trove_cat_edit','error_in_trove_operation'), db_error());
		}
		$res=db_query("DELETE FROM trove_cat WHERE trove_cat_id='$form_trove_cat_id'");
		if (!$res || db_affected_rows($res)<1) {
			exit_error( $Language->getText('admin_trove_cat_edit','error_in_trove_operation'), db_error());
		}
	}
	session_redirect("/admin/trove/trove_cat_list.php");
}

/*
	Main Code
*/

$res_cat = db_query("SELECT * FROM trove_cat WHERE trove_cat_id=$trove_cat_id");
if (db_numrows($res_cat)<1) {
	exit_error( $Language->getText('admin_trove_cat_edit','no_such_category'));
}
$row_cat = db_fetch_array($res_cat);

site_admin_header(array('title'=>$Language->getText('admin_trove_cat_edit','title')));
?>

<h3><?php echo $Language->getText('admin_trove_cat_edit','edit_trove_category'); ?></h3>

<form action="trove_cat_edit.php" method="post">

<p><?php echo $Language->getText('admin_trove_cat_edit','parent_category'); ?>
<br /><select name="form_parent">

<?php
// generate list of possible parents
$res_parent = db_query("SELECT shortname,fullname,trove_cat_id FROM trove_cat");

// Place the root node at the start of the list
print('<option value="0"');
if ($row_cat["parent"] == 0) {
	print(' selected="selected"');
}
print('>root</option>');
while ($row_parent = db_fetch_array($res_parent)) {
	print ('<option value="'.$row_parent["trove_cat_id"].'"');
	if ($row_cat["parent"] == $row_parent["trove_cat_id"]) print ' selected="selected"';
	print ('>'.$row_parent["fullname"]."</option>\n");
}

?>
</select>

<input type="hidden" name="form_trove_cat_id" value="<?php
  print $GLOBALS['trove_cat_id']; ?>" /></p>

<p><?php echo $Language->getText('admin_trove_cat_edit','new_category_short_name'); ?>:
<br /><input type="text" name="form_shortname" value="<?php print $row_cat["shortname"]; ?>" /></p>

<p><?php echo $Language->getText('admin_trove_cat_edit','new_category_full_name'); ?>:
<br /><input type="text" name="form_fullname" value="<?php print $row_cat["fullname"]; ?>" /></p>

<p><?php echo $Language->getText('admin_trove_cat_edit','new_category_description'); ?>:
<br /><input type="text" name="form_description" size="80" value="<?php print $row_cat["description"]; ?>" /></p>

<br /><input type="submit" name="submit" value="<?php echo $Language->getText('admin_trove_cat_edit','update'); ?>" /><input type="submit" name="delete" value="<?php echo $Language->getText('admin_trove_cat_edit','delete'); ?>" />
</form>

<?php

site_admin_footer(array());

?>
