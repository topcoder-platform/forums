#!/bin/bash
set -eo pipefail
APP_NAME=$1
CI_DEPLOY_TOKEN=$2
ENV=$3
BRANCH=$4
UPDATE_CACHE=""
echo "" > vanilla.env
if [ "$ENV" = "dev" ]; then
  ENV=$ENV CI_DEPLOY_TOKEN=$CI_DEPLOY_TOKEN BRANCH=$BRANCH docker-compose -f docker-compose.yml -f docker-compose.dev.yml build $APP_NAME
else
  ENV=$ENV CI_DEPLOY_TOKEN=$CI_DEPLOY_TOKEN BRANCH=$BRANCH docker-compose -f docker-compose.yml build $APP_NAME
fi
#docker create --name app $APP_NAME:latest