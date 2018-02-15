<?php
namespace O10n;

/**
 * Install Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Install extends Controller implements Controller_Interface
{
    private $current_version; // current plugin version

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        return parent::construct($Core, array(
            'cache',
            'options',
            //'pagecache'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        if (!defined('O10N_CORE_VERSION')) {
            throw new Exception('Installation error. Constant O10N_CORE_VERSION missing.', 'core');
        }

        // set current version
        $this->current_version = get_option('o10n_version', false);

        // upgrade/install hooks
        add_action('plugins_loaded', array($this, 'upgrade'), 10);

        // activate / deactivate hooks
        add_action('o10n_plugin_activate', array($this, 'activate'), 10);
        add_action('o10n_plugin_deactivate', array($this, 'deactivate'), 10);

        // add cron shedules
        add_filter('cron_schedules', array($this,'cron_schedules'));
    }

    /**
     * Activate plugin hook
     */
    final public function activate()
    {
        // setup crons
        if (function_exists('wp_next_scheduled')) {

            // cache cleanup cron
            if (!wp_next_scheduled('o10n_cron_prune_cache')) {
                wp_schedule_event(current_time('timestamp'), 'twicedaily', 'o10n_cron_prune_cache');
            }

            // cache expire cron
            if (!wp_next_scheduled('o10n_cron_prune_expired_cache')) {
                wp_schedule_event(current_time('timestamp'), '5min', 'o10n_cron_prune_expired_cache');
            }
        }
    }

    /**
     * Deactivate plugin hook
     */
    final public function deactivate()
    {

        // remove crons
        wp_clear_scheduled_hook('o10n_cron_prune_cache');
        wp_clear_scheduled_hook('o10n_cron_prune_expired_cache');
    }

    /**
     * Upgrade plugin
     */
    final public function upgrade()
    {

        // new installation
        if (!$this->current_version) {
            return $this->install();
        }

        // upgrade
        if (O10N_CORE_VERSION !== $this->current_version) {

            // define install flag
            $options = $this->options->get();
            
            // update options?
            $update_options = false;

            // upgrade from Above The Fold version (<3.0)
            if (version_compare($this->current_version, '3.0', '<')) {

                // load upgrade controller
                $upgrade = & Install_Upgrade_ABTF::load($this->core);

                // upgrade
                $options = $upgrade->upgrade($this->current_version, $options);
            }

            // update tables
            $this->create_tables();

            // update current version option
            update_option('o10n_version', O10N_CORE_VERSION, true);

            // ...

            // update options
            update_option('o10n', $options, true);

            // clear page related caches
            $this->pagecache->clear();
        }
    }

    /**
     * Install plugin
     */
    final protected function install()
    {

        // create tables
        $this->create_tables();

        // load default configuration
        // ...
        //update_option('o10n', $options, true);

        // set version option
        update_option('o10n_version', O10N_CORE_VERSION, false);
        $this->current_version = O10N_CORE_VERSION;
    }

    /**
     * Create cache table
     */
    final private function create_tables()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        /**
         * Cache table
         */
        $table_name = $this->cache->db_table();
        if (!$this->table_exists($table_name)) {
            $sql = "CREATE TABLE {$table_name} (
                `hash` binary(16) NOT NULL,
                `type` tinyint(1) UNSIGNED NOT NULL,
                `ext` VARCHAR(4) NOT NULL,
                `size` int(10) UNSIGNED NOT NULL,
                `date` datetime NOT NULL,
                `expire` int(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`hash`,`type`),
                KEY `type` (`type`),
                KEY `size` (`size`),
                KEY `date` (`date`),
                KEY `expire` (`expire`)
            );";
            dbDelta($sql);
        }
    }

    /**
     * Test if table exists
     *
     * @param  string $table_name The table name to verify.
     * @return bool   Table exists true/false
     */
    final private function table_exists($table_name)
    {
        return ($this->wpdb->get_var($this->wpdb->prepare("SHOW TABLES LIKE '%s'", $table_name)) === $table_name);
    }

    /**
     * Add cron shedules
     *
     * @param  array $schedules Cron schedules
     * @return array Modified cron schedules
     */
    final public function cron_schedules($schedules)
    {
        if (!isset($schedules["5min"])) {
            $schedules["5min"] = array(
                'interval' => 5 * 60,
                'display' => __('Once every 5 minutes'));
        }
        if (!isset($schedules["30min"])) {
            $schedules["30min"] = array(
                'interval' => 30 * 60,
                'display' => __('Once every 30 minutes'));
        }

        return $schedules;
    }
}
