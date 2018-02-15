<?php
namespace O10n;

/**
 * Web Font Optimization
 *
 * Advanced Web Font optimization toolkit. Font Face API, Web Font Observer, Google Font Loader, Critical CSS, async and timed font rendering and more.
 *
 * @link              https://pagespeed.pro/
 * @since             1.0
 * @package           o10n
 *
 * @wordpress-plugin
 * Plugin Name:       Web Font Optimization
 * Description:       Advanced Web Font optimization toolkit. Font Face API, Web Font Observer, Google Font Loader, Critical CSS, async and timed font rendering and more.
 * Version:           0.0.1
 * Author:            PageSpeed.pro
 * Author URI:        https://pagespeed.pro/
 * Text Domain:       o10n
 * Domain Path:       /languages
 */

if (! defined('WPINC')) {
    die;
}

// abort loading during upgrades
if (defined('WP_INSTALLING') && WP_INSTALLING) {
    return;
}

// settings
$module_version = '0.0.1';
$minimum_core_version = '0.0.1';
$plugin_path = dirname(__FILE__);

// load the optimization module loader
if (!class_exists('\O10n\Module')) {
    require $plugin_path . '/core/controllers/module.php';
}

// load module
new Module(
    'fonts',
    'Web Font Optimization',
    $module_version,
    $minimum_core_version,
    array(
        'core' => array(
            'http',
            'shutdown',
            'cache',
            'client',
            'install',
            'output',
            'fonts'
        ),
        'admin' => array(
            'AdminFonts'
        )
    ),
    __FILE__
);
