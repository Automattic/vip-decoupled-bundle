#!/usr/bin/env bash

apt-get update
apt-get install -y mariadb-client subversion
cd wp-content/plugins/vip-decoupled-bundle
./bin/install-wp-tests.sh tests-wordpress root password tests-mysql latest
./vendor/bin/phpunit
