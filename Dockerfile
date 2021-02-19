FROM webdevops/php-apache

ARG CI_DEPLOY_TOKEN
ARG VANILLA_VERSION=3.3
ARG ENV

# Environment Variables
ENV TIDEWAYS_SERVICE vanilla
ENV WEB_DOCUMENT_ROOT /vanillapp

# Get the latest release of Vanilla Forums
RUN wget https://github.com/vanilla/vanilla/releases/download/Vanilla_${VANILLA_VERSION}/vanilla-${VANILLA_VERSION}.zip
RUN unzip vanilla-${VANILLA_VERSION}.zip -d /tmp
RUN cp -r /tmp/package/. /vanillapp/
RUN chmod -R 777 /vanillapp

# Delete the auto-enabled 'stubcontent' plugin which adds stub contents
RUN rm -R /vanillapp/plugins/stubcontent
# Clone the forum-plugins repository
RUN git clone https://github.com/topcoder-platform/forums-plugins.git /tmp/forums-plugins

# Remove DebugPlugin from PROD env
# RUN if [ "$ENV" = "prod" ]; \
#    then rm -R /tmp/forums-plugins/DebugPlugin; \
# fi

# Copy the Filestack plugin
RUN git clone https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-filestack-plugin /tmp/forums-plugins/Filestack

# Copy the Groups plugin
RUN git clone https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-groups-plugin /tmp/forums-plugins/Groups

# Copy the SumoLogic plugin
RUN git clone https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-sumologic-plugin /tmp/forums-plugins/Sumologic

# Copy the TopcoderEditor plugin
RUN git clone https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-topcoder-editor-plugin /tmp/forums-plugins/TopcoderEditor

# Copy all plugins to the Vanilla plugins folder
RUN cp -r /tmp/forums-plugins/. /vanillapp/plugins

# Get the debug bar plugin
RUN wget https://us.v-cdn.net/5018160/uploads/addons/KSBIPJYMC0F2.zip
RUN unzip KSBIPJYMC0F2.zip
RUN cp -r debugbar /vanillapp/plugins

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
# Clone the forum-theme repository
RUN git clone 'https://github.com/topcoder-platform/forums-theme.git' /vanillapp/themes/topcoder

# Tideways
RUN apt-get update && apt-get install -y gnupg2
RUN echo 'deb https://packages.tideways.com/apt-packages debian main' > /etc/apt/sources.list.d/tideways.list && \
    curl -L -sS 'https://packages.tideways.com/key.gpg' | apt-key add - && \
    apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get -yq install tideways-php && \
    apt-get autoremove --assume-yes && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# For customization a placeholder /opt/docker/etc/php/php.ini is available which will be loaded as last configuration file.
# All settings can be overwritten in this ini file
RUN echo 'extension=tideways.so\ntideways.connection=tcp://tideways-daemon:9135\ntideways.enable_cli=0\n' >> opt/docker/etc/php/php.ini
