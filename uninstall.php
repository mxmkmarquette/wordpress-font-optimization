<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://pagespeed.pro/
 * @since      2.5.0
 *
 * @package    o10n
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// get O10N config
$options = get_option('o10n', false);

// remove webfont config
if ($options) {
    $param = 'fonts.';

    foreach ($options as $key => $value) {
        if (strpos($key, $param) === 0) {
            unset($options[$key]);
        }
    }

    // remove empty options
    if (empty($options)) {
        delete_option('o10n');
    }
}

// todo clear cache directory
