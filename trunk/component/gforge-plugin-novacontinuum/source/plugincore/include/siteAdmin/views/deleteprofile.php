<h2><?php echo dgettext ("gforge-plugin-novacontinuum", "title_site_admin"); ?><h2>

<?php

if($from == 'admin'){
	$urlPrefix = 'admin/';
	$groupIdAdding = '&group_id='.$group_id;
	$viewUrl = 'optionprivateinstance';
}else{
	$urlPrefix = 'siteAdmin/';
	$groupIdAdding = '';
	$viewUrl = 'optioninstance';
}

$menu_text = array ();
$menu_links = array ();
$menu_text [] = dgettext ('gforge-plugin-novacontinuum', 'return_site_admin');
$menu_links [] = '/plugins/novacontinuum/'.$urlPrefix.'index.php?view='.$viewUrl.'&instanceid='.$instanceid.$groupIdAdding;
	
echo $HTML->subMenu ($menu_text, $menu_links);
echo $HTML->boxTop (dgettext ("gforge-plugin-novacontinuum", "delete_profile"));
?><p />
<?php
if(isset($instanceid)&&isset($profileid)){
	$instance = $serviceManager->getContinuumInstance($instanceid);
	
	$profileToEdit = $serviceManager->getProfileForInstance($instance,$profileid);

	
	
	echo sprintf ( dgettext ('gforge-plugin-novacontinuum', 'confirm_delete_profile') , $profileToEdit->name);
?>
<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="deleteprofile">
	<input type="hidden" name="view" value="<?php echo $viewUrl;?>">
	<input type="hidden" name="instanceid" value="<?php echo $instanceid;?>">
	<input type="hidden" name="profileid" value="<?php echo $profileid;?>">
	<?php
		if($from == 'admin'){?>
	<input type="hidden" name="group_id" value="<?php echo $group_id;?>">	
	<?php
		}
	?>
	<input type="submit" name="deleteProfile" value="<? echo dgettext ("gforge-plugin-novacontinuum", "confirm_delete"); ?>" />
</form>

<?php	
}
echo $HTML->boxBottom ();
?>