FROM php:7.1-apache

# Install ClamAV
RUN apt-get -y -q -qq update && \
    apt-get -y -q -qq install -y clamav

# Update ClamAV DB.
COPY ./config/clamav/freshclam.conf /etc/clamav/freshclam.conf
RUN freshclam --quiet

COPY ./src /var/www/html