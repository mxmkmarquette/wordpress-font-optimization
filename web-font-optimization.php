<?php
namespace O10n;

/**
 * Web Font Optimization
 *
 * Advanced Web Font optimization toolkit. Font Face API, Web Font Observer, Google Font Loader, Critical CSS, HTTP/2 Server Push, async and timed font rendering and more.
 *
 * @link              https://github.com/o10n-x/
 * @package           o10n
 *
 * @wordpress-plugin
 * Plugin Name:       Web Font Optimization
 * Description:       Advanced Web Font optimization toolkit. Font Face API, Web Font Observer, Google Font Loader, Critical CSS, HTTP/2 Server Push, async and timed font rendering and more.
 * Version:           0.0.39
 * Author:            Optimization.Team
 * Author URI:        https://optimization.team/
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
$module_version = '0.0.39';
$minimum_core_version = '0.0.24';
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
            'client',
            'fonts'
        ),
        'admin' => array(
            'AdminFonts'
        )
    ),
    5,
    array(
        'google_webfonts_api' => array(
            'hash_dir' => 'fonts/google/',
            'file_ext' => '.json',
            'expire' => 86400 // expire after 1 day
        )
    ),
    __FILE__
);
