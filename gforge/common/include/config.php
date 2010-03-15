<?php
/**
 * FusionForge configuration functions
 *
 * Copyright 2009, Roland Mas
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

if (!isset ($fusionforge_config)) {
	$fusionforge_config = array () ;
}

function fusionforge_get_config ($section, $var) {
	global $fusionforge_config ;

	if (!isset ($fusionforge_config[$section])
	    || !isset ($fusionforge_config[$section][$var])) {
		return false ;
	}
	
	return $fusionforge_config[$section][$var] ;
}

function fusionforge_define_config_item ($section, $var, $default) {
	global $fusionforge_config ;

	if (!isset ($fusionforge_config[$section])) {
		$fusionforge_config[$section] = array () ;
	}

	if (!isset ($fusionforge_config[$section][$var])) {
		$fusionforge_config[$section][$var] = $default ;
	}
}

function fusionforge_read_config_file ($file) {
	global $fusionforge_config ;

	$sections = parse_ini_file ($file, true) ;
	foreach ($sections as $sectname => $options) {
		if (!isset ($fusionforge_config[$section]))
			continue ;
		foreach ($options as $key => $value) {
			if (!isset ($fusionforge_config[$section][$var]))
				continue ;
			$fusionforge_config[$sectname][$key] = $value ;
		}
	}

	return ;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
