FROM webdevops/php-apache

ARG VANILLA_VERSION=3.3
ENV WEB_DOCUMENT_ROOT /vanillapp

# Get the latest release of Vanilla Forums
RUN wget https://github.com/vanilla/vanilla/releases/download/Vanilla_${VANILLA_VERSION}/vanilla-${VANILLA_VERSION}.zip
RUN unzip vanilla-${VANILLA_VERSION}.zip -d /tmp
RUN cp -r /tmp/package/. /vanillapp/
RUN chmod -R 777 /vanillapp

# Clone the forum-plugins repository
RUN git clone https://github.com/topcoder-platform/forums-plugins.git /tmp/forums-plugins
RUN cp /tmp/forums-plugins/config/vanilla.config-defaults.php /vanillapp/conf/config-defaults.php
RUN cp -r /tmp/forums-plugins/. /vanillapp/plugins


