<?php
/*
 * Copyright (C) 2010 Alcatel-Lucent
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The test suite ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once dirname(dirname(__FILE__)).'/Testing/SeleniumGforge.php';

class SvnCommit2Tracker extends FForge_SeleniumTestCase
{
	function testSvnCommit2Tracker()
	{
		/*
		 * Create a projectA,
		 * Activate SCM & commit plugin.
		 * Make a commit
		 * Verify that commit email has been correctly send/stored.
		 */
		$this->populateStandardTemplate();
		$this->initSvn();
		$this->activatePlugin('svntracker');
		$this->login(FORGE_ADMIN_USERNAME);

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Admin");
		$this->clickAndWait("link=Tools");
		$this->click("use_svntracker");
		$this->clickAndWait("submit");

		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Tracker");
		$this->clickAndWait("link=Bugs");
		$this->clickAndWait("link=Submit New");
		$this->click("summary");
		$this->type("summary", "Bug Summary1");
		$this->type("details", "Description1");
		$this->clickAndWait("submit");

		// Run the svn create to get the repository with the hooks.
		$this->cron("cronjobs/create_scm_repos.php");

		$svn = "svn --non-interactive --no-auth-cache";
		$url = URL."svn/projecta/";

		// Try accessing the svn repository using admin rights
		// => Should be allowed.
		system("rm -fr /tmp/svn.test");
		mkdir("/tmp/svn.test");

		system("cd /tmp/svn.test; $svn --username ".FORGE_ADMIN_USERNAME." --password ".FORGE_ADMIN_PASSWORD." co $url >/dev/null", $ret);
		$this->assertEquals($ret, 0);

		system("echo 'this is a simple text' > /tmp/svn.test/projecta/mytext.txt");

		system("cd /tmp/svn.test/projecta; $svn add mytext.txt >/dev/null", $ret);
		$this->assertEquals($ret, 0);

		system("cd /tmp/svn.test/projecta; $svn --username ".FORGE_ADMIN_USERNAME." --password ".FORGE_ADMIN_PASSWORD." ci -m 'Fixed [#1] added mytext' >/dev/null", $ret);
		$this->assertEquals($ret, 0);

		system("echo 'with a new line' >> /tmp/svn.test/projecta/mytext.txt");

		system("cd /tmp/svn.test/projecta; $svn --username ".FORGE_ADMIN_USERNAME." --password ".FORGE_ADMIN_PASSWORD." ci -m 'Improved [#1] updated mytext' >/dev/null", $ret);
		$this->assertEquals($ret, 0);

		$this->clickAndWait("link=Bug Summary1");
		$this->assertTextPresent("added mytext");

		$this->click("link=Commits");
		$this->clickAndWait("link=Diff To 2");
		$this->assertTextPresent("Diff of /mytext.txt");
		$this->assertTextPresent("with a new line");

		// Same test for tasks, pattern to use is [T2]
		$this->open(ROOT);
		$this->clickAndWait("link=ProjectA");
		$this->clickAndWait("link=Tasks");
		$this->clickAndWait("link=To Do");
		$this->clickAndWait("link=Add Task");
		$this->type("summary", "Task Summary2");
		$this->type("details", "Description2");
		$this->clickAndWait("submit");

		system("echo 'Summary2 completed' >> /tmp/svn.test/projecta/mytext.txt");

		system("cd /tmp/svn.test/projecta; $svn --username admin --password myadmin ci -m '[T2] done' >/dev/null", $ret);
		$this->assertEquals($ret, 0);

		$this->clickAndWait("link=To Do");
		$this->clickAndWait("link=Task Summary2");
		$this->assertTextPresent("[T2] done");

		system("rm -fr /tmp/svn.test");
	}
}
?>