# Scribbit

A tiny, self-hosted web app for tracking ideas and projects in markdown files -- no database or cloud service required.  
It is a complete rewrite of the [nemex project](https://github.com/neonelephantstudio/nemex), built using the [Slim Framework](https://github.com/slimphp/Slim).

## Installation

Download the zip file:

    curl -LOk https://github.com/justindisney/scribbit/archive/master.zip && unzip master.zip -d ./scribbit && rm master.zip

Or clone the repository:

    git clone https://github.com/justindisney/scribbit.git

An install script included in the project will run composer and set some directory permissions:

    cd scribbit && ./install.sh

Change the `USER` and `PASSWORD` values in `config.php`.

Installation is complete!

## Use the Application

The simplest way to access the app is to go to `http://example.com/scribbit/public' in a browser.

A more sophisticated method would be to set up a subdomain with its docroot set to `/path/to/my/scribbit/public`, then load `http://scribbit.example.com` in a browser.

If installed correctly, a login screen should appear. Use the credentials that were set in `config.php` to login.

## Caveats

At the moment, caching for the twig templates is disabled, due to the possibility that the cache files may not be removable from the command-line.

File and image upload functionality is coming soon.

## How to Contribute

### Pull Requests

1. Fork the Scribbit repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the `develop` branch

It is very important to separate new features or improvements into separate feature branches, and to send a
pull request for each branch. This allows us to review and pull in new features or improvements individually.

### Style Guide

All pull requests must adhere to the [PSR-2 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).
