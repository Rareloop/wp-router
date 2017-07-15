<?php
/*
Plugin Name: WP Router
Description: Router for handling custom requests
Version: 1.0.0
Author: Rareloop (https://www.rareloop.com)
*/

// If we haven't loaded this plugin from Composer we need to add our own autoloader
if (!class_exists('Rareloop\WordPress\Router\Router')) {
    // Get a reference to our PSR-4 Autoloader function that we can use to add our
    // Acme namespace
    $autoloader = require_once('autoload.php');

    // Use the autoload function to setup our class mapping
    $autoloader('Rareloop\\WordPress\\Router\\', __DIR__ . '/src/');
}

Rareloop\WordPress\Router\Router::init();
