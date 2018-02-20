<?php
namespace O10n;

/**
 * Controller class
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

abstract class Controller
{
    private static $instances = array(); // instantiated controllers

    protected $allow_public = false; // allow public access?

    protected $core; // core controller

    // controller instances
    protected $json;
    protected $tools;
    protected $i18n;
    protected $error;
    protected $install;
    protected $client;
    protected $options;
    protected $env;
    protected $cache;
    protected $file;
    protected $http;
    protected $output;
    protected $db;
    protected $admin;
    protected $pwa;
    protected $csp;
    protected $hash;
    protected $shutdown;
    protected $url;
    protected $http2;
    protected $proxy;
    protected $html;
    protected $css;
    protected $js;

    // admin controller instances
    protected $AdminCP;
    protected $AdminLinkFilter;
    protected $AdminPluginIndex;
    protected $AdminMenu;
    protected $AdminClient;
    protected $AdminOptions;
    protected $AdminAjax;
    protected $AdminView;
    protected $AdminForm;
    protected $AdminForminput;
    protected $AdminHTML;
    protected $AdminScreen;
    protected $AdminHelp;
    protected $AdminEditor;

    protected $wpdb; // WordPress database

    private $bind_after_setup; // controllers to bind after setup

    // base controllers to bind
    private $base_controllers = array();

    protected $first_priority; // first priority integer
    protected $content_path; // wp-content/ directory path

    /**
     * Construct the controller
     *
     * @param Core  &$Core The root optimization controller.
     * @param array $bind  An array with controllers to bind to the child.
     */
    final protected function __construct(Core $Core, $bind)
    {
        global $wpdb; // WordPress database
        $this->wpdb = $wpdb; // WordPress database controller
        $this->core = $Core; // core controller

        // first priority integer
        $this->first_priority = (PHP_INT_MAX * -1);

        // wp-content/ path
        $this->content_path = trailingslashit(WP_CONTENT_DIR);

        // bind non existent controllers after setup
        $this->bind_after_setup = array();

        // bind child controllers
        $bind = (!empty($bind)) ? array_unique(array_merge($this->base_controllers, $bind)) : $this->base_controllers;
        foreach ($bind as $controller_name) {
            $controller_classname = 'O10n\\' . ucfirst($controller_name);
            if (isset(self::$instances[$controller_classname])) {
                if (!property_exists($this, $controller_name)) {
                    throw new Exception('Child controller not protected in Controller: ' . $controller_name, 'core');
                }
                $this->$controller_name = & self::$instances[$controller_classname];
            } else {
                $this->bind_after_setup[$controller_classname] = $controller_name;
            }
        }

        // bind controllers after setup
        if (!empty($this->bind_after_setup)) {
            add_action('o10n_controller_setup_completed', array($this,'after_controller_setup'), $this->first_priority, 1);
            add_action('o10n_setup_completed', array($this,'after_optimization_setup'), $this->first_priority);
        }
    }

    /**
     * Construct the controller
     *
     * @param  Core       &$Core The root optimization controller.
     * @param  array      $bind  An array with controllers to bind to the child.
     * @return Controller The instantiated controller.
     */
    final protected static function &construct(Core $Core, $bind = false)
    {
        // verify calling controller
        $controller_classname = get_called_class();
        if (substr($controller_classname, 0, 5) !== 'O10n\\' && substr($controller_classname, 0, 8) !== 'O10nDev\\') {
            throw new Exception('Invalid caller.', 'core');
        }

        // allow instantiation once
        if (isset(self::$instances[$controller_classname])) {

            // developer debug message
            _doing_it_wrong($controller_classname, __('Forbidden'), O10N_CORE_VERSION);

            // print error to regular users
            wp_die('The '.htmlentities($controller_classname, ENT_COMPAT, 'utf-8').' controller is instantiated multiple times. This may indicate an attack. Please contact the administrator of this website.');
        }

        // instantiate controller
        self::$instances[$controller_classname] = new $controller_classname($Core, $bind);

        // setup controller
        if (method_exists(self::$instances[$controller_classname], 'setup')) {
            self::$instances[$controller_classname]->setup();
        }

        // controller setup completed
        do_action('o10n_controller_setup_completed', $controller_classname);

        // return controller
        return self::$instances[$controller_classname];
    }

    /**
     * After optimization controller setup hook.
     *
     * @param string $controller_classname The class name of the controller to bind.
     */
    final public function after_controller_setup($controller_classname)
    {

        // bind child controller directly after instantiation and setup
        if (!isset($this->bind_after_setup[$controller_classname])) {

            // development class override
            if (strpos($controller_classname, 'O10nDev\\') !== false) {
                $controller_classname = str_replace('O10nDev\\', 'O10n\\', $controller_classname);
            } else {
                return;
            }
        }

        if (isset($this->bind_after_setup[$controller_classname])) {
            if (!isset(self::$instances[$controller_classname])) {
                throw new Exception('Controller ' . $controller_classname . ' not instantiated.', 'core');
            }
            $controller_name = $this->bind_after_setup[$controller_classname];

            // admin controller?
            
            $this->$controller_name = & self::$instances[$controller_classname];
            unset($this->bind_after_setup[$controller_classname]);
        }
    }

    /**
     * After Core optimization controller setup hook.
     */
    final public function after_optimization_setup()
    {

        // bind child controllers and throw exception for unmet dependencies
        if (!empty($this->bind_after_setup)) {
            foreach ($this->bind_after_setup as $controller_classname => $controller_name) {
                if (isset(self::$instances[$controller_classname])) {
                    $this->$controller_name = & self::$instances[$controller_classname];
                } else {
                    throw new Exception('Failed to bind controller ' . $controller_name . '.', 'core');
                }
            }
        }
    }

    /**
     * Return public access
     */
    final public function allow_public()
    {
        return $this->allow_public;
    }
}

/**
 * Controller interface
 */
interface Controller_Interface
{
    public static function load(Core $Core); // the method to instantiate the controller
}
