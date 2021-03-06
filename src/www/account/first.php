<?php
/**
 * Welcome page
 *
 * This is the page user is redirected to after first site login
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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

$forge_name = forge_get_config ('forge_name');
site_user_header(array('title'=>sprintf(_('Welcome to %s'), $forge_name)));

print '<p>' . sprintf(_('You are now a registered user on %s, the online development environment for Open Source projects.'), $forge_name) . '</p>';

print '<p>' . sprintf(_('As a registered user, you can participate fully in the activities on the site. You may now post messages to the project message forums, post bugs for software in %s, sign on as a project developer, or even start your own project.'), $forge_name) . '</p>';

print '<p>';
printf(_('-- the %s staff'), $forge_name);
print '</p>';

site_user_footer();
