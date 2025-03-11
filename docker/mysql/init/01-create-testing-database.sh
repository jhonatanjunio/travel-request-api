#!/bin/bash
set -e

mysql -u root -p${MYSQL_ROOT_PASSWORD} <<-EOSQL
    CREATE DATABASE IF NOT EXISTS travel_management_testing;
    GRANT ALL PRIVILEGES ON travel_management_testing.* TO '${MYSQL_USER}'@'%';
EOSQL 