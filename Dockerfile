FROM webdevops/php-apache

ARG CI_DEPLOY_TOKEN
ARG VANILLA_VERSION=3.3
ARG ENV

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

# Remove SqlPlugin from PROD env
RUN if [ "$ENV" = "prod" ]; \
    then rm -R /tmp/forums-plugins/SqlPlugin; \
    fi

# Copy the Filestack plugin
RUN git clone https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-filestack-plugin /tmp/forums-plugins/Filestack

#Copy the Groups plugin
RUN git clone https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-groups-plugin /tmp/forums-plugins/Groups

#Copy the SumoLogic plugin
RUN git clone https://${CI_DEPLOY_TOKEN}@github.com/topcoder-platform/forums-sumologic-plugin /tmp/forums-plugins/Sumologic

# Copy all plugins to the Vanilla plugins folder
RUN cp -r /tmp/forums-plugins/. /vanillapp/plugins

#Get the debug bar plugin
RUN wget https://us.v-cdn.net/5018160/uploads/addons/KSBIPJYMC0F2.zip
RUN unzip KSBIPJYMC0F2.zip
RUN cp -r debugbar /vanillapp/plugins

# Install Topcoder dependencies
RUN composer install --working-dir /vanillapp/plugins/Topcoder
# Install Filestack dependencies
RUN composer install --working-dir /vanillapp/plugins/Filestack
# Install Groups dependencies
RUN composer install --working-dir /vanillapp/plugins/Groups
# Copy Vanilla configuration files
COPY ./config/vanilla/. /vanillapp/conf/.
# Copy Topcoder Vanilla files
COPY ./vanilla/. /vanillapp/.
# Set permissions on config file
RUN chown application:application /vanillapp/conf/config.php
RUN chmod ug=rwx,o=rx /vanillapp/conf/config.php
# Clone the forum-theme repository
RUN git clone 'https://github.com/topcoder-platform/forums-theme.git' /vanillapp/themes/topcoder
