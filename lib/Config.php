<?php

define( 'APP_PATH', __DIR__ . '/../' );

class Config
{
    const APP_NAME            = 'Scribbit';
    const USER                = 'scribbit';
    const PASSWORD            = 'zipzap';
    const FILE_CREATION_MODE  = 0777;
    const DATE_FORMAT         = 'Y-m-d H:i:s';
    const SCRIBBITS_DIRECTORY = 'scribbits/'; //use a trailing slash
    const LOST_AND_FOUND      = '__lost+found';
    // Maximum width for Images. Beyond this, a scaled down version is created
    const IMAGE_BIG_PATH     = 'big/';
    const IMAGE_MAX_WIDTH    = 800;
    const IMAGE_JPEG_QUALITY = 95;
    const IMAGE_SHARPEN      = true;
    // The TIMEZONE setting is only used if there's no explicit timezone set in the php.ini
    const TIMEZONE = 'US/Pacific';

}

// Set the timezone if we don't have one
if (@date_default_timezone_get()) {
    date_default_timezone_set(Config::TIMEZONE);
}