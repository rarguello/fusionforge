#!/bin/sh
. tests/scripts/common-functions
. tests/scripts/common-vm

export FORGE_HOME=/usr/share/gforge
get_config $@
prepare_workspace
destroy_vm -t centos5 $@
start_vm_if_not_keeped -t centos5 $@

# BUILD FUSIONFORGE REPO
echo "Build FUSIONFORGE REPO in $BUILDRESULT"
make -f Makefile.rh BUILDRESULT=$BUILDRESULT RPM_TMP=$RPM_TMP all

setup_ff_repo $@
setup_dag_repo $@

sleep 5
ssh root@$HOST "FFORGE_DB=$DB_NAME FFORGE_USER=gforge FFORGE_ADMIN_USER=$FORGE_ADMIN_USERNAME FFORGE_ADMIN_PASSWORD=$FORGE_ADMIN_PASSWORD export FFORGE_DB FFORGE_USER FFORGE_ADMIN_USER FFORGE_ADMIN_PASSWORD; yum install -y --skip-broken fusionforge fusionforge-plugin-scmsvn fusionforge-plugin-svntracker fusionforge-plugin-online_help fusionforge-plugin-extratabs fusionforge-plugin-authldap fusionforge-plugin-scmgit fusionforge-plugin-blocks"

ssh root@$HOST '(echo [core];echo use_ssl=no) > /etc/gforge/config.ini.d/zzz-buildbot.ini'
ssh root@$HOST "su - postgres -c \"pg_dumpall\" > /root/dump"
# Install a fake sendmail to catch all outgoing emails.
ssh root@$HOST "perl -spi -e s#/usr/sbin/sendmail#$FORGE_HOME/tests/scripts/catch_mail.php# /etc/gforge/config.ini.d/defaults.ini"

# Stop cron
ssh root@$HOST "service crond stop" || true

# Install selenium tests
ssh root@$HOST mkdir $FORGE_HOME/tests
make -C 3rd-party/selenium selenium-server.jar
cp 3rd-party/selenium/selenium-server.jar tests/
rsync -a --delete tests/ root@$HOST:$FORGE_HOME/tests/

ssh root@$HOST "cat > $FORGE_HOME/tests/config/phpunit" <<-EOF
HUDSON_URL=$HUDSON_URL
JOB_NAME=$JOB_NAME
EOF

# Run tests
retcode=0
echo "Run phpunit test on $HOST in $FORGE_HOME"

ssh root@$HOST "yum install -y vnc-server ; mkdir -p /root/.vnc"
ssh root@$HOST "cat > /root/.vnc/xstartup ; chmod +x /root/.vnc/xstartup" <<EOF
#! /bin/bash
: > /root/phpunit.exitcode
$FORGE_HOME/tests/scripts/phpunit.sh func &> /var/log/phpunit.log &
echo \$! > /root/phpunit.pid
wait %1
echo \$? > /root/phpunit.exitcode
EOF
ssh root@$HOST vncpasswd <<EOF
password
password
EOF
ssh root@$HOST "vncserver :1"
sleep 5
pid=$(ssh root@$HOST cat /root/phpunit.pid)
ssh root@$HOST "tail -f /var/log/phpunit.log --pid=$pid"
sleep 5
retcode=$(ssh root@$HOST cat /root/phpunit.exitcode)
rsync -av root@$HOST:/var/log/ $WORKSPACE/reports/
ssh root@$HOST "vncserver -kill :1" || retcode=$?

stop_vm_if_not_keeped -t centos5 $@
exit $retcode
