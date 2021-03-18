#!/bin/sh
MEMCACHED_SERVER_HOST=$(echo $MEMCACHED_SERVER| cut -d':' -f 1)
MEMCACHED_SERVER_PORT=$(echo $MEMCACHED_SERVER| cut -d':' -f 2)
echo flush_cache.sh: connecting to $MEMCACHED_SERVER
echo flush_all | nc -q5 $MEMCACHED_SERVER_HOST $MEMCACHED_SERVER_PORT
nc_exit_code=$?
if [  $nc_exit_code != 0 ]; then
  echo flush_cache.sh: exit code $nc_exit_code
else
  echo flush_cache.sh: connection was made and completed successfully
fi