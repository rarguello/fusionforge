#!/bin/bash

# This script runs the preferred functionnal test suite, using phpunit
# and Selenium, which will test the Web interface of FusionForge in a
# controlled Firefox browser.

# Build an unofficial package for selenium and install it
if ! dpkg -l selenium | grep -q ^ii ; then
    cd /usr/src/fusionforge/3rd-party/selenium/selenium
    debian/rules get-orig-source
    debuild --no-lintian --no-tgz-check -us -uc
    dpkg -i /usr/src/fusionforge/3rd-party/selenium/selenium_*_all.deb

    # Selenium dependencies
    aptitude -y install default-jre iceweasel

fi

config_path=$(cd /usr/src/fusionforge;utils/forge_get_config_basic fhs config_path)

(echo [mediawiki]; echo unbreak_frames=yes) > $config_path/config.ini.d/zzz-buildbot.ini

# Test dependencies
aptitude -y install php5-cli phpunit phpunit-selenium

## If available, install the JUnit OSLC provider test suite
#if [ -d src/plugins/oslc/tests ]; then
#    cd /usr/src/fusionforge/src/plugins/oslc/tests
#    ./setup-provider-test.sh
#fi

# Now, start the functionnal test suite using phpunit and selenium
/usr/src/fusionforge/tests/scripts/phpunit.sh deb/debian
