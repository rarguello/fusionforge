<?php
/** FusionForge Git plugin
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

class GitPlugin extends SCMPlugin {
	function GitPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmgit';
		$this->text = 'Git';
		# $this->hooks[] = 'scm_update_repolist' ;
		$this->hooks[] = 'scm_browser_page' ;
		# $this->hooks[] = 'scm_gather_stats' ;
		$this->hooks[] = 'scm_generate_snapshots' ;
		
		require_once $gfconfig.'plugins/scmgit/config.php' ;
		
		$this->default_git_server = $default_git_server ;
		$this->git_root = $git_root;
		
		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_git_server ;
	}

	function getBlurb () {
		return _('<p>This GIT plugin is not completed yet.</p>') ;
	}

	function getInstructionsForAnon ($project) {
		$b =  _('<p><b>Anonymous Git Access</b></p><p>This project\'s Git repository can be checked out through anonymous access with the following command.</p>');
		$b .= '<p>' ;
		$b .= '<tt>git clone '.util_make_url ('/anonscm/git/'.$project->getUnixName().'/').'</tt><br />';
		$b .= '</p>';
		return $b ;
	}

	function getInstructionsForRW ($project) {
		$b = _('<p><b>Developer GIT Access via SSH</b></p><p>Only project developers can access the GIT tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
		$b .= '<p><tt>git clone git+ssh://<i>'._('developername').'</i>@' . $project->getSCMBox() . ':'. $this->git_root .'/'. $project->getUnixName().'</tt></p>' ;
		return $b ;
	}

	function getSnapshotPara ($project) {
		global $sys_scm_snapshots_path ;
		$b = "" ;
		$filename = $project->getUnixName().'-scm-latest.tar.gz';
		if (file_exists($sys_scm_snapshots_path.'/'.$filename)) {
			$b .= '<p>[' ;
			$b .= util_make_link ("/snapshots.php?group_id=".$project->getID(),
					      _('Download the nightly snapshot')
				) ;
			$b .= ']</p>';
		}
		return $b ;
	}

	function printBrowserPage ($params) {
		global $HTML;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if ($project->usesPlugin ($this->name)) {
			if ($this->browserDisplayable ($project)) {
				print '<iframe src="'.util_make_url ("/plugins/scmgit/cgi-bin/gitweb.cgi?r=".$project->getUnixName()).'" frameborder="no" width=100% height=700></iframe>' ;
			}
		}
	}

	function getBrowserLinkBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Git Repository Browser'));
		$b .= _('<p>Browsing the Git tree gives you a view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.</p>');
		$b .= '<p>[' ;
		$b .= util_make_link ("/scm/browser.php?group_id=".$project->getID(),
				      _('Browse Git Repository')
			) ;
		$b .= ']</p>' ;
		return $b ;
	}

// 	function getStatsBlock ($project) {
// 		global $HTML ;
// 		$b = '' ;

// 		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (commits>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id ORDER BY combined DESC, realname',
// 					  array ($project->getID()));
		
// 		if (db_numrows($result) > 0) {
// 			$b .= $HTML->boxMiddle(_('Repository Statistics'));

// 			$tableHeaders = array(
// 				_('Name'),
// 				_('Adds'),
// 				_('Commits')
// 				);
// 			$b .= $HTML->listTableTop($tableHeaders);
			
// 			$i = 0;
// 			$total = array('adds' => 0, 'commits' => 0);
			
// 			while($data = db_fetch_array($result)) {
// 				$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
// 				$b .= '<td width="50%">' ;
// 				$b .= util_make_link_u ($data['user_name'], $data['user_id'], $data['realname']) ;
// 				$b .= '</td><td width="25%" align="right">'.$data['adds']. '</td>'.
// 					'<td width="25%" align="right">'.$data['commits'].'</td></tr>';
// 				$total['adds'] += $data['adds'];
// 				$total['commits'] += $data['commits'];
// 				$i++;
// 			}
// 			$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
// 			$b .= '<td width="50%"><strong>'._('Total').':</strong></td>'.
// 				'<td width="25%" align="right"><strong>'.$total['adds']. '</strong></td>'.
// 				'<td width="25%" align="right"><strong>'.$total['commits'].'</strong></td>';
// 			$b .= '</tr>';
// 			$b .= $HTML->listTableBottom();
// 			$b .= '<hr size="1" />';
// 		}

// 		return $b ;
// 	}
	function getStatsBlock ($project) {
		return ;
	}

	function createOrUpdateRepo ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
				
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		$project_name = $project->getUnixName() ;
		$repo = $this->git_root . '/' . $project_name ;
		$unix_group = 'scm_' . $project_name ;

		system ("mkdir -p $repo") ;
		if (!is_file ("$repo/HEAD") && !is_dir("$repo/objects") && !is_dir("$repo/refs")) {
			system ("GIT_DIR=\"$repo\" git --bare init") ;
			system ("echo \"Git repository for $project_name\" > $repo/description") ;
		}

		system ("chgrp -R $unix_group $repo") ;
		if ($project->enableAnonSCM()) {
			system ("chmod -R g+wXs,o+rX-w $repo") ;
		} else {
			system ("chmod -R g+wXs,o-rwx $repo") ;
		}
	}

	function updateRepositoryList ($params) {
		$groups = $this->getGroups () ;
		$list = array () ;
		foreach ($groups as $project) {
			if ($this->browserDisplayable ($project)) {
				$list[] = $project ;
			}
		}

/*		$fname = '/etc/gforge/plugins/scmdarcs/config.py' ;

		$f = fopen ($fname.'.new', 'w') ;
		foreach ($list as $project) {
			$classname = str_replace ('-', '_',
						  'repo_' . $project->getUnixName()) ;
			
			$repo = $this->git_root . '/' . $project->getUnixName() ;
			fwrite ($f, "class: $classname\n"
				."\treponame = $classname\n"
			       ."\trepodir = $repo\n"
				."\trepourl = " . util_make_url ('/anonscm/git/'.$project->getUnixName().'/') . "\n"
				."\trepoprojurl = " . util_make_url ('/projects/'.$project->getUnixName().'/') . "\n"
				. "\n") ;
		}
		fclose ($f) ;
		chmod ($fname.'.new', 0644) ;
		rename ($fname.'.new', $fname) ;
*/
	}

	function generateSnapshots ($params) {
		global $sys_scm_tarballs_path ;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		$group_name = $project->getUnixName() ;

		$snapshot = $sys_scm_snapshots_path.'/'.$group_name.'-scm-latest.tar.gz';
		$tarball = $sys_scm_tarballs_path.'/'.$group_name.'-scmroot.tar.gz';

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			unlink ($tarball) ;
			return false;
		}

		$toprepo = $this->git_root ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		if (!is_dir ($repo)) {
			unlink ($tarball) ;
			return false ;
		}

		$today = date ('Y-m-d') ;
		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}

		system ("git archive --format=tar --prefix=$group_name-scm-$today/ HEAD | gzip > $tmp/snapshot.tar.gz");
		chmod ("$tmp/snapshot.tar.gz", 0644) ;
		copy ("$tmp/snapshot.tar.gz", $snapshot) ;
		unlink ("$tmp/snapshot.tar.gz") ;

		system ("tar czCf $toprepo $tmp/tarball.tar.gz " . $project->getUnixName()) ;
		chmod ("$tmp/tarball.tar.gz", 0644) ;
		copy ("$tmp/tarball.tar.gz", $tarball) ;
		unlink ("$tmp/tarball.tar.gz") ;
		system ("rm -rf $tmp") ;
	}
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
