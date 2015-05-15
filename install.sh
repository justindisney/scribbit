#!/bin/sh

if command -v composer 2>/dev/null; then
    echo "we have composer"
else
    echo "composer not installed; downloading composer.phar"
#    curl -sS https://getcomposer.org/installer | php -d allow_url_fopen=On
#    php -d allow_url_fopen=On composer.phar update
fi
