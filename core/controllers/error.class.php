<?php
namespace O10n;

/**
 * Error Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Error Controller
 */
class Error extends Controller implements Controller_Interface
{
    
    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            // controllers to bind
            'file',
            'client'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
    }

    /**
     * Handle error exception
     */
    final public function handle(Exception $error)
    {
        $category = $error->getCategory();
        if (!isset($this->config[$category])) {
            $this->config[$category] = array(
                'admin_notice' => true
            );
        }

        // display admin notice?
        $admin_notice = $error->isAdminNotice();
        if ($admin_notice === -1) {
            $admin_notice = $this->config[$category]['admin_notice'];
        }

        // admin notice
        if ($admin_notice) {
            $this->add_notice($error->getMessage(), $category);
        }

        if (defined('O10N_DEBUG') && O10N_DEBUG && isset($this->client) && $category !== 'client') {
            $this->client->print_exception($category, $error->getMessage());
        }
    }

    /**
     * Get admin error notices
     */
    final public function get_notices()
    {
        return get_option('o10n_notices', array());
    }

    /**
     * Add admin error notice
     */
    final public function add_notice($message, $category, $type = 'ERROR')
    {
        // get notices
        $notices = $this->get_notices();

        // notice data
        $notice = array();
        $notice['hash'] = md5($category . ':' . $message);
        $notice['text'] = $message;
        $notice['category'] = $category;
        $notice['type'] = $type;
        $notice['date'] = time();

        // verify if notice exists
        $updated_notices = array();
        foreach ($notices as $key => $item) {

            // notice exist, merge and push to front
            if (isset($item['hash']) && $item['hash'] === $notice['hash']) {
                $notice = array_merge($item, $notice);
                continue 1;
            }
            $updated_notices[] = $item;
        }

        // add stack trace for plugin development
        /*if ($this->dev->is_plugin_dev()) {
            $notice['trace'] = json_encode(debug_backtrace(), JSON_PRETTY_PRINT);
        }*/

        // push to front
        array_unshift($updated_notices, $notice);

        // sort by date
        usort($updated_notices, function ($a1, $a2) {
            return $a2['date'] - $a1['date'];
        });

        // limit amount of stored notices
        if (count($updated_notices) > 10) {
            $updated_notices = array_slice($updated_notices, -10, 10);
        }

        // save notices
        update_option('o10n_notices', $updated_notices, false);
    }

    /**
     * Print fatal error
     *
     * @todo
     *
     * @param mixed $error Error to display.
     */
    final public function fatal($error)
    {
        // clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // output SEO friendly header (temporary error)
        if (!headers_sent()) {
            header(($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1' ? 'HTTP/1.1' : 'HTTP/1.0') . ' 503 Service Temporarily Unavailable', true, 503);
            header('Retry-After: 60');
        }

        // Exception
        if (is_a($error, 'O10n\\Exception')) {
            $error = $error->getMessage();
        }

        // try 503.php in theme directory
        $custom_errorpage = $this->file->theme_directory() . '503.php';
        if (file_exists($custom_errorpage)) {
            require $custom_errorpage;
            exit;
        }

        wp_die('<h1>Fatal Error</h1>' . $error);
    }
}
