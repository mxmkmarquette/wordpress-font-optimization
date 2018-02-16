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

$cache_table_name = 'o10n__cache';
$cache_index_table_name = 'o10n__cache_index';

// remove O10N cache
$table_exists = ($this->wpdb->get_var($this->wpdb->prepare("SHOW TABLES LIKE '%s'", $cache_table_name)) === $cache_table_name);
if (!$table_exists) {

    // delete cache entries for font store
    $sql = "CREATE TABLE `".$cache_table_name."` (
      `store`    INTEGER NOT NULL,
      `hash`    BINARY(16) NOT NULL,
      `hash_a`  BIGINT(20) NOT NULL,
      `hash_b`  BIGINT(20) NOT NULL,
      `date`,  INTEGER,
      `size`  INTEGER,
      PRIMARY KEY (`store`,`hash`)
    );
    CREATE UNIQUE INDEX `store_hash_int` ON `".$cache_table_name."` (`store`,`hash_a`,`hash_b`);
    CREATE INDEX `store` ON `".$cache_table_name."` (`store`);
    CREATE INDEX `date` ON `".$cache_table_name."` (`date`);";

    dbDelta($sql);
}

// verify if cache index table exists
$table_exists = ($this->wpdb->get_var($this->wpdb->prepare("SHOW TABLES LIKE '%s'", $cache_index_table_name)) === $cache_index_table_name);
if (!$table_exists) {

    // create hash ID index table
    $sql = "CREATE TABLE `".$cache_index_table_name."` (
      `id`    INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
      `store`    INTEGER NOT NULL,
      `hash`    BINARY(16) NOT NULL,
      `hash_a`  BIGINT(20) NOT NULL,
      `hash_b`  BIGINT(20) NOT NULL,
      `date`,  INTEGER,
      `size`  INTEGER,
      `suffix`    VARCHAR(100) NOT NULL
    );
    CREATE UNIQUE INDEX `store_hash` ON `".$cache_index_table_name."` (`store`,`hash`);
    CREATE UNIQUE INDEX `store_hash_int` ON `".$cache_table_name."` (`store`,`hash_a`,`hash_b`);
    CREATE INDEX `store` ON `".$cache_index_table_name."` (`store`);
    CREATE INDEX `date` ON `".$cache_index_table_name."` (`date`);";

    dbDelta($sql);