#! /usr/bin/php -f
<?php
/**
 * FusionForge
 *
 * Copyright 2012, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

if (count ($argv) < 2) {
        echo "Usage: .../list-projects-using-plugin.php <plugin>
For instance: .../list-projects-using-plugin.php mediawiki
" ;
        exit (1) ;
}

$pname = $argv[1] ;

require (dirname(__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';

// Plugins subsystem
require_once($gfcommon.'include/Plugin.class.php');
require_once($gfcommon.'include/PluginManager.class.php');

setup_plugin_manager () ;

$plugin = plugin_get_object($pname);

if (!$plugin) {
	die ("Wrong plugin name\n") ;
}

foreach ($plugin->getGroups() as $p) {
	print $p->getUnixName()."\n";
}
?>
