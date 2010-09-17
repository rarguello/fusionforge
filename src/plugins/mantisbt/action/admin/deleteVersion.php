<?php

/*
 * Copyright 2010, Capgemini
 * Authors: Franck Villaume - capgemini
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

/* deleteVersion action page */

$deleteVersion = $_POST['deleteVersion'];

if ($deleteVersion) {
    try {
	    $clientSOAP = new SoapClient("http://".forge_get_config('server','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
	    $clientSOAP->__soapCall('mc_project_version_delete', array("username" => $username, "password" => $password, "version_id" => $deleteVersion));
    } catch (SoapFault $soapFault) {
        $msg = 'Erreur : '.$soapFault->faultstring;
        session_redirect('plugins/mantisbt/?type=admin&id='.$id.'&pluginname=mantisbt&error_msg='.urlencode($msg));
    }
    $feedback = 'Op&eacute;ration r&eacute;ussie';
    session_redirect('plugins/mantisbt/?type=admin&id='.$id.'&pluginname=mantisbt&feedback='.urlencode($feedback));
} else {
    $warning = 'Param&egravetres manquants';
    session_redirect('plugins/mantisbt/?type=admin&id='.$id.'&pluginname=mantisbt&warning_msg='.urlencode($warning));
}
?>
