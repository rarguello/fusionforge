<?php
/*
 * Copyright (C) 2012 Roland Mas
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

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumForge.php';

class ScmGitWUITest extends FForge_SeleniumTestCase
{
	function testScmGitWUI()
	{
		$this->skip_on_src_installs();
		$this->skip_on_rpm_installs();

		$this->activatePlugin('scmgit');
		$this->populateStandardTemplate('empty');
		$this->init();

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->click("//input[@name='scmradio' and @value='scmgit']");
		$this->clickAndWait("submit");
	    
		$this->type("//input[@name='repo_name']", "other-repo");
		$this->type("//input[@name='description']", "Description for second repository");
		$this->clickAndWait("//input[@value='Submit']");
		$this->assertTextPresent("New repository other-repo registered");
		
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=SCM");
		$this->assertTextPresent("other-repo");

		$this->assertTextPresent("Anonymous Access to the Git");
		$this->clickAndWait("link=Request a personal repository");
		$this->assertTextPresent("You have now requested a personal Git repository");

		// Run the cronjob to create repositories
		$this->cron("create_scm_repos.php");

		$this->clickAndWait("link=SCM");
		$this->assertTextPresent("Access to your personal repository");

		$this->open(ROOT.'/plugins/scmgit/cgi-bin/gitweb.cgi?a=project_list;pf=projecta');
		$this->waitForPageToLoad();
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");
		$this->assertTextPresent("other-repo.git");
		$this->assertTextPresent("users/".FORGE_ADMIN_USERNAME.".git");

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->clickAndWait("link=Source Code Admin");
		$this->clickAndWait("//form[@name='form_delete_repo_other-repo']/input[@value='Delete']");
		$this->assertTextPresent("Repository other-repo is marked for deletion");

		// Run the cronjob to create repositories
		$this->cron("create_scm_repos.php");

		$this->open(ROOT.'/plugins/scmgit/cgi-bin/gitweb.cgi?a=project_list;pf=projecta');
		$this->waitForPageToLoad();
		$this->assertElementPresent("//.[@class='page_footer']");
		$this->assertTextPresent("projecta.git");
		$this->assertTextNotPresent("other-repo.git");
		$this->assertTextPresent("users/".FORGE_ADMIN_USERNAME.".git");
	}

	/**
	 * Method that is called after Selenium actions.
	 *
	 * @param  string $action
	 */
	protected function defaultAssertions($action)
	{
		if ($action == 'waitForPageToLoad') {
			$this->assertTrue($this->isElementPresent("//h1")
					  || $this->isElementPresent("//.[@class='page_footer']"));
		}
	}

}
?>
