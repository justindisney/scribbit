#!/bin/sh

if command -v composer 2>/dev/null; then
    echo "composer installed"
    composer update --no-dev
else
    echo "composer not installed; downloading composer.phar"
    curl -sS https://getcomposer.org/installer | php -d allow_url_fopen=On
    php -d allow_url_fopen=On composer.phar update --no-dev
fi

chmod -R 0777 scribbits/
chmod -R 0777 templates/cache
