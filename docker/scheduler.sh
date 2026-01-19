#!/bin/sh
# Docker Scheduler Script - runs Laravel scheduler in a loop
# This is for use inside a Docker container

cd /var/www/html

while true
do
    php artisan schedule:run --verbose --no-interaction >> /var/log/scheduler.log 2>&1
    sleep 60
done
