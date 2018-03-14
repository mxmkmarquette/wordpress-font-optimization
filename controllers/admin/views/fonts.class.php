<?php
namespace O10n;

/**
 * Webfont Optimization Admin View Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminViewFonts extends AdminViewBase
{
    protected static $view_key = 'fonts'; // reference key for view
    protected $module_key = 'fonts';

    // default tab view
    private $default_tab_view = 'intro';

    // google-webfonts-helper API
    // @link https://github.com/majodev/google-webfonts-helper
    private $google_webfonts_api_url = 'https://google-webfonts-helper.herokuapp.com/api/fonts';

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @param  string     $View View key.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'json',
            'file',
            'http',
            'options',
            'cache',
            'AdminAjax',
            'AdminClient',
            'AdminOptions',
            'url'
        ));
    }
    
    /**
     * Setup controller
     */
    protected function setup()
    {
        // WPO plugin
        if (defined('O10N_WPO_VERSION')) {
            $this->default_tab_view = 'optimization';
        }

        // set view etc
        parent::setup();
    }

    /**
     * Setup view
     */
    public function setup_view()
    {
        // process form submissions
        add_action('o10n_save_settings_verify_input', array( $this, 'verify_input' ), 10, 1);

        // retrieve google font info from API
        add_action('wp_ajax_o10n_fonts_google_api_info', array( $this, 'ajax_google_api_info'), 10);

        // install font in theme directory
        add_action('wp_ajax_o10n_fonts_google_api_install', array( $this, 'ajax_google_api_install'), 10);

        // enqueue scripts
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), $this->first_priority);

        // create critical CSS
        if (isset($_GET['create-critical-css'])) {
            add_action('admin_init', array( $this, 'create_critical_css' ), 10, 1);
        }
    }

    /**
     * Return help tab data
     */
    final public function help_tab()
    {
        $data = array(
            'name' => __('Web Font Optimization', 'o10n'),
            'github' => 'https://github.com/o10n-x/wordpress-font-optimization',
            'wordpress' => 'https://wordpress.org/plugins/web-font-optimization/',
            'docs' => 'https://github.com/o10n-x/wordpress-font-optimization/tree/master/docs'
        );

        return $data;
    }

    /**
     * Create Critical CSS for web font CSS
     */
    final public function create_critical_css()
    {
        $themedir = $this->file->theme_directory(array('critical-css'));
        $file = $themedir . 'webfonts.css';

        // create critical css file
        if (!file_exists($file)) {
            $this->file->put_contents($file, ' ');
        }

        $critical_css_files = $this->options->get('css.critical.files');

        // add new file
        if (!isset($critical_css_files['webfonts.css'])) {
            $critical_css_files['webfonts.css'] = array(
                'file' => 'webfonts.css',
                'filepath' => $file,
                'priority' => 1,
                'title' => 'Web Fonts'
            );

            try {
                $this->AdminOptions->save(array('css.critical.files' => $critical_css_files));
            } catch (Exception $err) {
                wp_die($err->getMessage());
            }
        }

        wp_redirect(add_query_arg(array('page' => 'o10n-css-editor','file' => 'critical-css/webfonts.css'), admin_url('admin.php')));
    }

    /**
     * Enqueue scripts and styles
     */
    final public function enqueue_scripts()
    {
        // skip if user is not logged in
        if (!is_admin() || !is_user_logged_in()) {
            return;
        }

        // set module path
        $this->AdminClient->set_config('module_url', $this->module->dir_url());

        // global admin script
        $this->AdminClient->preload_CodeMirror('custom-theme-only');
        $tab = (isset($_REQUEST['tab'])) ? trim($_REQUEST['tab']) : $this->default_tab_view;
        switch ($tab) {
            case "optimization":

                // preload editors
                if ($this->options->bool('fonts.fontface.enabled') || $this->options->bool('fonts.fontfaceobserver.enabled')) {
                    $this->AdminClient->preload_JSONEditor();
                }
                if ($this->options->bool('fonts.googlefontloader.enabled')) {
                    $this->AdminClient->preload_CodeMirror('js');
                }
            break;
            case "google":

                // Google fonts json
                // @link https://google-webfonts-helper.herokuapp.com/api/fonts
                try {
                    $google_fonts_json = $this->json->parse(file_get_contents($this->module->dir_path() . 'includes/google-fonts.json'), true);
                } catch (\Exception $err) {
                    throw new Exception('Failed to parse google-fonts.json: ' . $err->getMessage(), 'core');
                }

                // add Google Fonts to client
                $this->AdminClient->set_config('google_fonts', $google_fonts_json);

                $this->AdminClient->set_config('google_fonts_example', 'ABCČĆDĐEFGHIJKLMNOPQRSŠTUVWXYZŽ abcčćdđefghijklmnopqrsštuvwxyzž 1234567890 ‘?’“!”(%)[#]{@}/&\<-+÷×=>®©$€£¥¢:;,.*...');
            break;
            default:
                
            break;
        }

        // styles
        wp_enqueue_style('o10n_view_fonts', $this->module->dir_url() . 'admin/css/view-fonts.css');

        // scripts
        wp_enqueue_script('o10n_view_fonts', $this->module->dir_url() . 'admin/js/view-fonts.js', array( 'jquery', 'o10n_cp' ), $this->module->version());
    }


    /**
     * Return view template
     */
    public function template($view_key = false)
    {

        // template view key
        $view_key = false;

        $tab = (isset($_REQUEST['tab'])) ? trim($_REQUEST['tab']) : $this->default_tab_view;
        switch ($tab) {
            case "optimization":
                $view_key = 'fonts';
            break;
            case "google":
            case "settings":
            case "intro":
                $view_key = 'fonts-' . $tab;
            break;
            default:
                throw new Exception('Invalid view ' . esc_html($view_key), 'core');
            break;
        }

        return parent::template($view_key);
    }
    
    /**
     * Verify settings input
     *
     * @param  object   Form input controller object
     */
    final public function verify_input($forminput)
    {
        // Web Font Optimization

        $tab = (isset($_REQUEST['tab'])) ? trim($_REQUEST['tab']) : 'o10n';
        switch ($tab) {
            case "optimization":

                $forminput->type_verify(array(
                    'fonts.fontface.enabled' => 'bool',

                    'fonts.fontfaceobserver.enabled' => 'bool',
                    'fonts.fontfaceobserver.config' => 'json-array',

                    'fonts.googlefontloader.enabled' => 'bool',
                    'fonts.googlefontloader.remove' => 'bool',

                    'fonts.http2_push.enabled' => 'bool',

                    'fonts.remove_linked.enabled' => 'bool',

                    'fonts.cdn.enabled' => 'bool',
                    'fonts.cdn.http2_push' => 'bool'
                ));

                // font face API
                if ($forminput->bool('fonts.fontface.enabled')) {
                    $forminput->type_verify(array(
                        'fonts.fontface.config' => 'json-array',
                        'fonts.fontface.rel_preload' => 'bool',
                        'fonts.fontface.requestAnimationFrame' => 'bool',
                        'fonts.fontface.load_position' => 'string',
                        'fonts.fontface.render_timing.enabled' => 'bool'
                    ));
                    
                    // load timing
                    if ($forminput->get('fonts.fontface.load_position') === 'timing') {
                        $forminput->type_verify(array(
                            'fonts.fontface.load_timing.type' => 'string'
                        ));

                        if ($forminput->get('fonts.fontface.load_timing.type') === 'requestAnimationFrame') {
                            $forminput->type_verify(array(
                                'fonts.fontface.load_timing.frame' => 'int-empty'
                            ));
                        }
                
                        if ($forminput->get('fonts.fontface.load_timing.type') === 'inview') {
                            $forminput->type_verify(array(
                                'fonts.fontface.load_timing.selector' => 'string',
                                'fonts.fontface.load_timing.offset' => 'int-empty'
                            ));
                        }

                        if ($forminput->get('fonts.fontface.load_timing.type') === 'media') {
                            $forminput->type_verify(array(
                                'fonts.fontface.load_timing.media' => 'string'
                            ));
                        }
                    }

                    // render timing
                    if ($forminput->bool('fonts.fontface.render_timing.enabled')) {
                        $forminput->type_verify(array(
                            'fonts.fontface.render_timing.type' => 'string'
                        ));

                        if ($forminput->get('fonts.fontface.render_timing.type') === 'requestAnimationFrame') {
                            $forminput->type_verify(array(
                            'fonts.fontface.render_timing.frame' => 'int-empty'
                        ));
                        }
            
                        if ($forminput->get('fonts.fontface.render_timing.type') === 'inview') {
                            $forminput->type_verify(array(
                            'fonts.fontface.render_timing.selector' => 'string',
                            'fonts.fontface.render_timing.offset' => 'int-empty'
                        ));
                        }

                        if ($forminput->get('fonts.fontface.render_timing.type') === 'media') {
                            $forminput->type_verify(array(
                            'fonts.fontface.render_timing.media' => 'string'
                        ));
                        }
                    }
                }

                // font face observer
                if ($forminput->bool('fonts.fontfaceobserver.enabled')) {
                    $forminput->type_verify(array(
                        'fonts.fontfaceobserver.config' => 'json-array',
                        'fonts.fontfaceobserver.load_position' => 'string'
                    ));

                    // load timing
                    if ($forminput->get('fonts.fontfaceobserver.load_position') === 'timing') {
                        $forminput->type_verify(array(
                            'fonts.fontfaceobserver.load_timing.type' => 'string'
                        ));

                        if ($forminput->get('fonts.fontfaceobserver.load_timing.type') === 'requestAnimationFrame') {
                            $forminput->type_verify(array(
                                'fonts.fontfaceobserver.load_timing.frame' => 'int-empty'
                            ));
                        }
                
                        if ($forminput->get('fonts.fontfaceobserver.load_timing.type') === 'inview') {
                            $forminput->type_verify(array(
                                'fonts.fontfaceobserver.load_timing.selector' => 'string',
                                'fonts.fontfaceobserver.load_timing.offset' => 'int-empty'
                            ));
                        }

                        if ($forminput->get('fonts.fontfaceobserver.load_timing.type') === 'media') {
                            $forminput->type_verify(array(
                                'fonts.fontfaceobserver.load_timing.media' => 'string'
                            ));
                        }
                    }
                }

                // google font loader
                if ($forminput->bool('fonts.googlefontloader.enabled')) {
                    $forminput->type_verify(array(
                        'fonts.googlefontloader.config' => 'string',
                        'fonts.googlefontloader.load_position' => 'string'
                    ));

                    // verify config
                    $config = trim($forminput->get('fonts.googlefontloader.config', ''));
                    if ($config) {
                        $regex = '|^((var\s+)?WebFontConfig\s*=)|si';
                        if (preg_match($regex, $config)) {
                            $config = trim(preg_replace($regex, '', $config));
                        }
                        if (!preg_match('|^\{.*\}\s*;?$|si', $config)) {
                            $forminput->error('', __('Failed to recognize WebFontConfig variable. The correct format is <code>WebFontConfig = { ... }</code>.', 'o10n'));
                        }
                        $forminput->set('fonts.googlefontloader.config', $config);
                    }

                    // load timing
                    if ($forminput->get('fonts.googlefontloader.load_position') === 'timing') {
                        $forminput->type_verify(array(
                            'fonts.googlefontloader.load_timing.type' => 'string'
                        ));

                        if ($forminput->get('fonts.googlefontloader.load_timing.type') === 'requestAnimationFrame') {
                            $forminput->type_verify(array(
                                'fonts.googlefontloader.load_timing.frame' => 'int-empty'
                            ));
                        }
                
                        if ($forminput->get('fonts.googlefontloader.load_timing.type') === 'inview') {
                            $forminput->type_verify(array(
                                'fonts.googlefontloader.load_timing.selector' => 'string',
                                'fonts.googlefontloader.load_timing.offset' => 'int-empty'
                            ));
                        }

                        if ($forminput->get('fonts.googlefontloader.load_timing.type') === 'media') {
                            $forminput->type_verify(array(
                                'fonts.googlefontloader.load_timing.media' => 'string'
                            ));
                        }
                    }
                }

                // remove linked fonts
                if ($forminput->bool('fonts.remove_linked.enabled')) {
                    $forminput->type_verify(array(
                        'fonts.remove_linked.filter.enabled' => 'bool',
                        'fonts.remove_linked.filter.list' => 'newline_array'
                    ));
                }

                // font CDN
                if ($forminput->bool('fonts.cdn.enabled')) {
                    $forminput->type_verify(array(
                        'fonts.cdn.url' => 'string',
                        'fonts.cdn.mask' => 'string'
                    ));
                }

                // critical css file
                if ($forminput->get('fonts.critical-css-file') !== '') {
                }

            break;
            case "settings":

                // Font profile
                $fonts = $forminput->get('fonts', 'json-array');
                if ($fonts) {

                    // @todo improve
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveArrayIterator($fonts),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );
                    $path = [];
                    $flatArray = [];

                    $arrayVal = false;
                    foreach ($iterator as $key => $value) {
                        $path[$iterator->getDepth()] = $key;

                        $dotpath = 'fonts.'.implode('.', array_slice($path, 0, $iterator->getDepth() + 1));
                        if ($arrayVal && strpos($dotpath, $arrayVal) === 0) {
                            continue 1;
                        }

                        if (!is_array($value) || empty($value) || array_keys($value)[0] === 0) {
                            if (is_array($value) && (empty($value) || array_keys($value)[0] === 0)) {
                                $arrayVal = $dotpath;
                            } else {
                                $arrayVal = false;
                            }

                            $flatArray[$dotpath] = $value;
                        }
                    }

                    // delete existing options
                    $this->options->delete('fonts.*');

                    // replace all options
                    $this->AdminOptions->save($flatArray);
                }
            break;
            default:
                throw new Exception('Invalid Web Font admin view ' . esc_html($tab), 'core');
            break;
        }
    }

    /**
     * Retrieve Google Font info from API
     */
    final public function ajax_google_api_info()
    {
        // process AJAX request
        $request = $this->AdminAjax->request();

        // font ID
        $font = $request->data('font');

        $charsets = $request->data('charsets');
        $variants = $request->data('variants');

        // verify input
        if (!$font) {
            $request->output_errors('no font ID');
        }
        if (!$charsets || !$variants) {
            $request->output_errors('no font charsets / styles');
        }

        // get font info from cache
        $cacheHashData = array($font,explode(',', $charsets),explode(',', $variants));
        if (!empty($cacheHashData[1])) {
            sort($cacheHashData[1]);
        }
        if (!empty($cacheHashData[2])) {
            sort($cacheHashData[2]);
        }

        $cacheHash = md5(json_encode($cacheHashData));
        $cacheFile = $this->cache->path('fonts', 'google_webfonts_api', $cacheHash);

        // verify cache data
        $fontInfo = false;
        if ($cacheFile) {
            $fontInfo = $this->cache->get('fonts', 'google_webfonts_api', $cacheHash, true);
            if ($fontInfo) {
                try {
                    $fontInfo = $this->json->parse($fontInfo, true);
                } catch (\Exception $err) {
                    $fontInfo = false;
                }
            } else {
                $fontInfo = false;
            }
        }

        if (!$fontInfo) {
            $url = $this->google_webfonts_api_url . '/' . $font . '?subsets=' . $charsets . '&variants=' . $variants;
            try {
                $responseData = $this->http->get($url);
            } catch (HTTPException $e) {
                $request->output_errors('API request failed: ' . esc_url($url) . ' Status: '.$e->getStatus().' Error: ' . $e->getMessage());
            }

            // invalid status code
            if ($responseData[0] !== 200) {
                $request->output_errors('API request failed: ' . esc_url($url) . ' Status: '.$responseData[0]);
            }

            $fontInfo = $responseData[1];

            try {
                $fontInfo = $this->json->parse($fontInfo, true);
            } catch (\Exception $err) {
                $request->output_errors('failed to parse API JSON response: ' . $err->getMessage());
            }

            // save cache
            $this->cache->put('fonts', 'google_webfonts_api', $cacheHash, json_encode($fontInfo), false, true);
        }

        $request->output_ok(false, $fontInfo);
    }


    /**
     * Install Google Font in theme directory
     */
    final public function ajax_google_api_install()
    {
        // process AJAX request
        $request = $this->AdminAjax->request();

        // verify if PHP supports ZIP
        if (!class_exists('ZipArchive')) {
            $request->output_errors('Your PHP installation does not support <a href="http://php.net/manual/en/ziparchive.extractto.php" target="_blank">ZipArchive</a>.');
        }

        // font ID
        $font = $request->data('font');

        $charsets = $request->data('charsets');
        $variants = $request->data('variants');

        // verify input
        if (!$font) {
            $request->output_errors('no font ID');
        }
        if (!$charsets || !$variants) {
            $request->output_errors('no font charsets / styles');
        }

        // construct ZIP download link
        $url = $url = $this->google_webfonts_api_url . '/' . $font . '?download=zip&subsets=' . $charsets . '&variants=' . $variants;

        // start download
        try {
            $responseData = $this->http->get($url);
        } catch (HTTPException $e) {
            $request->output_errors('Failed to download font zip package from API: ' . esc_url($url) . ' Status: '.$e->getStatus().' Error: ' . $e->getMessage());
        }

        // invalid status code
        if ($responseData[0] !== 200) {
            $request->output_errors('API request failed: ' . esc_url($url) . ' Status: '.$responseData[0]);
        }

        // headers
        $headers = wp_remote_retrieve_headers($responseData[2]);
        if (empty($headers) || !isset($headers['content-disposition']) || !preg_match('|filename=(.*\.zip)|Ui', $headers['content-disposition'], $out)) {
            $request->output_errors('The API did not return a valid response. Zip URL: <a href="'.$url.'">'.$url.'</a>');
        }
        $zip_filename = $out[1];

        // write zipfile to font directory
        try {
            $fontsdir = $this->file->theme_directory('fonts');
        } catch (Exception $err) {
            $request->output_errors('Failed to create <code>'.get_template().'/fonts/</code>: ' . $err->getMessage());
        }

        if (file_exists($fontsdir . $zip_filename)) {
            @unlink($fontsdir . $zip_filename);
        }

        // save zip file
        try {
            $this->file->put_contents($fontsdir . $zip_filename, $responseData[1]);
        } catch (\Exception $err) {
            $request->output_errors($err->getMessage());
        }

        // try to open zip
        $zip = new \ZipArchive;
        if ($zip->open($fontsdir . $zip_filename) !== true) {
            $request->output_errors('PHP ZipArchive failed to read Google Font zip-file.');
        } else {
            $zip->extractTo($fontsdir);
            $zip->close();

            // read fonts directory
            $files = new \FilesystemIterator($fontsdir, \FilesystemIterator::SKIP_DOTS);
            foreach ($files as $fileinfo) {

                // filename
                $filename = $fileinfo->getFilename();

                // view already loaded
                if (!$fileinfo->isFile() || !preg_match('#\.(eot|woff|woff2|svg|ttf)$#Ui', $filename)) {
                    continue 1;
                }

                // set permissions
                @chmod($fontsdir . $filename, O10N_THEME_CHMOD_FILE);
            }

            @unlink($fontsdir . $zip_filename);
        }

        $fontInfo = $responseData[1];

        $request->output_ok(__('The font package <a href="'.$url.'" download="'.$zip_filename.'">'.$zip_filename.'</a> has been installed in <code>'.get_template().'/fonts/</code>.', 'o10n'));
    }
}
