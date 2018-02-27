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

    // controller instances to allow in child
    protected $bind;

    protected $wpdb; // WordPress database

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
        if ($bind && is_array($bind)) {
            $this->bind = $bind;
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
     * Return public access
     */
    final public function allow_public()
    {
        return $this->allow_public;
    }

    /**
     * Get controller
     *
     * @param string $controller_name Property name of controller.
     */
    public function __get($controller_name)
    {
        if ($this->bind && in_array($controller_name, $this->bind)) {
            $controller_classname = 'O10n\\' . ucfirst($controller_name);
            if (isset(self::$instances[$controller_classname])) {
                return self::$instances[$controller_classname];
            }
        }

        if (!isset(self::$instances[0])) {
            self::$instances[0] = false;
        }

        return self::$instances[0];
    }
}

/**
 * Controller interface
 */
interface Controller_Interface
{
    public static function load(Core $Core); // the method to instantiate the controller
}
