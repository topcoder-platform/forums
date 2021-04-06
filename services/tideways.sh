#!/bin/sh
echo tideways.sh: Tideways Daemon for $TIDEWAYS_ENVIRONMENT
/etc/init.d/tideways-daemon start
exit_code=$?
if [  $exit_code != 0 ]; then
  echo tideways.sh: exit code $exit_code
else
  echo tideways.sh: started successfully
fi