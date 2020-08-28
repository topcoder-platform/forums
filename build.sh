#!/bin/bash
set -eo pipefail
APP_NAME=$1
UPDATE_CACHE=""
echo "" > vanilla.env
docker-compose -f docker-compose.yml build $APP_NAME
#docker create --name app $APP_NAME:latest