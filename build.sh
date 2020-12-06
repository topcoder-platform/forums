#!/bin/bash
set -eo pipefail
APP_NAME=$1
CI_DEPLOY_TOKEN=$2
ENV=$3
UPDATE_CACHE=""
echo "" > vanilla.env
ENV=$ENV CI_DEPLOY_TOKEN=$CI_DEPLOY_TOKEN docker-compose -f docker-compose.yml build $APP_NAME
#docker create --name app $APP_NAME:latest