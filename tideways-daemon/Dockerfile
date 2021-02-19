FROM debian:stable-slim

ARG TIDEWAYS_ENVIRONMENT_DEFAULT=production
ENV TIDEWAYS_ENVIRONMENT=$TIDEWAYS_ENVIRONMENT_DEFAULT

RUN useradd --system tideways
RUN apt-get update && apt-get install -yq --no-install-recommends gnupg2 curl sudo ca-certificates

RUN echo 'deb https://packages.tideways.com/apt-packages debian main' > /etc/apt/sources.list.d/tideways.list && \
    curl -L -sS 'https://packages.tideways.com/key.gpg' | apt-key add -
RUN DEBIAN_FRONTEND=noninteractive apt-get update && apt-get install -yq tideways-daemon && \
    apt-get autoremove --assume-yes && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

EXPOSE 9135

USER tideways

ENTRYPOINT ["tideways-daemon","--hostname=tideways-daemon","--address=0.0.0.0:9135"]