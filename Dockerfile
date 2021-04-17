FROM webdevops/php-apache

ARG CI_DEPLOY_TOKEN
ARG VANILLA_VERSION=3.3
ARG ENV
ARG BRANCH
ARG TIDEWAYS_ENV

ENV TIDEWAYS_SERVICE web
ENV TIDEWAYS_ENVIRONMENT=$TIDEWAYS_ENV
ENV TIDEWAYS_DAEMON_EXTRA="--env=$TIDEWAYS_ENVIRONMENT --debug"
ENV WEB_DOCUMENT_ROOT /vanillapp

RUN echo "Tideways Daemon for '$TIDEWAYS_ENV' env"

# Get the latest release of Vanilla Forums
RUN wget https://github.com/vanilla/vanilla/releases/download/Vanilla_${VANILLA_VERSION}/vanilla-${VANILLA_VERSION}.zip
RUN unzip vanilla-${VANILLA_VERSION}.zip -d /tmp
RUN cp -r /tmp/package/. /vanillapp/
RUN chmod -R 777 /vanillapp

# Delete the auto-enabled 'stubcontent' plugin which adds stub contents
RUN rm -R /vanillapp/plugins/stubcontent

RUN echo "'$BRANCH' branch will be used for dependency repos ..."

# Clone the forum-plugins repository
RUN git clone --branch ${BRANCH} https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-plugins.git /tmp/forums-plugins

# Copy the Filestack plugin
RUN git clone --branch ${BRANCH} https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-filestack-plugin /tmp/forums-plugins/Filestack

# Copy the Groups plugin
RUN git clone --branch ${BRANCH} https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-groups-plugin /tmp/forums-plugins/Groups

# Copy the SumoLogic plugin
RUN git clone --branch ${BRANCH} https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-sumologic-plugin /tmp/forums-plugins/Sumologic

# Copy the TopcoderEditor plugin
RUN git clone --branch ${BRANCH} https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-topcoder-editor-plugin /tmp/forums-plugins/TopcoderEditor

# Copy the forum-theme repository
RUN git clone --branch ${BRANCH} https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-theme.git /vanillapp/themes/topcoder

# Remove DebugPlugin from PROD env
# RUN if [ "$ENV" = "prod" ]; \
#    then rm -R /tmp/forums-plugins/DebugPlugin; \
#    fi


# Copy all plugins to the Vanilla plugins folder
RUN cp -r /tmp/forums-plugins/. /vanillapp/plugins

# Get the debug bar plugin
RUN if [ "$ENV" = "dev" ]; then \
    wget https://us.v-cdn.net/5018160/uploads/addons/KSBIPJYMC0F2.zip; \
    unzip KSBIPJYMC0F2.zip; \
    cp -r debugbar /vanillapp/plugins; \
fi

# Install Topcoder dependencies
RUN composer install --working-dir /vanillapp/plugins/Topcoder
# Install Filestack dependencies
RUN composer install --working-dir /vanillapp/plugins/Filestack
# Install Groups dependencies
RUN composer install --working-dir /vanillapp/plugins/Groups
# Install TopcoderEditor dependencies
RUN composer install --working-dir /vanillapp/plugins/TopcoderEditor
# Copy Vanilla configuration files
COPY ./config/vanilla/. /vanillapp/conf/.
# Copy Topcoder Vanilla files
COPY ./vanilla/. /vanillapp/.
# Set permissions on config file
RUN chown application:application /vanillapp/conf/config.php
RUN chmod ug=rwx,o=rx /vanillapp/conf/config.php

# Tideways
RUN apt-get update && apt-get install -yq --no-install-recommends gnupg2;
RUN echo 'deb https://packages.tideways.com/apt-packages-main any-version main' > /etc/apt/sources.list.d/tideways.list && \
    curl -L -sS 'https://packages.tideways.com/key.gpg' | apt-key add - && \
    apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get -yq install tideways-php tideways-daemon && \
    apt-get autoremove --assume-yes && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*; \
    echo 'extension=tideways.so\ntideways.enable_cli=0\ntideways.sample_rate=25' >> opt/docker/etc/php/php.ini;

# Copy custom supervisor's configs and scripts
# Netcat is used to connect to a memcached server
RUN apt-get update && apt-get install -y netcat
COPY ./services/*.conf /opt/docker/etc/supervisor.d/
COPY ./services/*.sh /opt/docker/bin/service.d/

# Ensure the service files are already executable
RUN chmod +x /opt/docker/bin/service.d/flush_cache.sh
RUN chmod +x /opt/docker/bin/service.d/tideways.sh