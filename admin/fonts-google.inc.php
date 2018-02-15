<?php
namespace O10n;

/**
 * Web Font optimization admin template
 *
 * @package    optimization
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH') || !defined('O10N_ADMIN')) {
    exit;
}

// theme directory path
$themepath = trailingslashit(preg_replace('|http(s)?://[^/]+/|i', '/', get_stylesheet_directory_uri())) . 'fonts/';
if ($get('fonts.cdn.enabled') === true) {
    $cdnurl = $view->url->cdn($themepath, array($get('fonts.cdn.url'),$get('fonts.cdn.mask')));
} else {
    $cdnurl = $themepath;
}

// print form header
$this->form_start(__('Google Font Downloader', 'o10n'), 'fonts');

?>
<style>
.selectize-dropdown, .selectize-dropdown.form-control{
    height: 50vh !important;
}

.selectize-dropdown-content{
    max-height: 100% !important;
    height: 100% !important;
}
</style>
<table class="form-table" id="font-config">
    <tr valign="top">
        <td>
        <p class="poweredby">Powered by <a href="https://github.com/majodev/google-webfonts-helper" target="_blank">google-webfonts-helper</a><span class="star"><a class="github-button" data-manual="1" href="https://github.com/majodev/google-webfonts-helper" data-icon="octicon-star" data-show-count="true" aria-label="Star majodev/google-webfonts-helper on GitHub">Star</a></span></p>

<h1>Google Font Downloader</h1>
<p>This tool enables to install Google Fonts locally in your theme directory to optimize loading with methods such as Font Face API or a custom CDN.</p>
<div class="google_font_select">
<select placeholder="<?php esc_attr_e('Search a Google Font...', 'o10n'); ?>"><option value=""></option></select>
<p class="description">Select a Google font to download.</p>
</div>

<div id="google_font_download_form" class="suboption" style="display:none;">
<h1 class="font-name"><span></span> <a href="https://fonts.google.com/" target="_blank" rel="noopener" title="View font on Google.com"><img src="<?php print O10N_URI;?>admin/images/google-fonts-logo.png"></a></h1>
<h3 class="font-category"></h3>

<p class="description">Select charsets</p>
<div class="font-charsets"></div>

<div class="suboption">
    <p class="description">Select styles</p>

    <table class="font-styles">
        <tbody>
            
            <tr>
                <td class="type"><label><input type="checkbox"> latin</label></td>
                <td class="example"><input type="text" value="The quick brown fox jumps over the lazy dog."></td>
            </tr>
        </tbody>
    </table>
</div>


<div class="suboption">
    <button type="button" id="theme_download" class="button button-large button-primary">Download to theme directory</button>
    <button type="button" id="zip_download" class="button button-large">Zip Download (browser download)</button>
</div>
<p class="suboption">The active theme directory where the fonts will be installed is <code><?php print get_template(); ?>/fonts/</code>.</p>

<p class="suboption info_yellow"><strong><span class="dashicons dashicons-lightbulb"></span></strong> The Google Font download API is hosted for free on <a href="https://www.heroku.com/" target="_blank" rel="noopener">Heroku</a>. Read the story about the API <a href="https://mranftl.com/2014/12/23/self-hosting-google-web-fonts/" target="_blank" rel="noopener">here</a>.</p>

<br />
<h1>Installation</h1>
<p class="description">To use Google fonts locally you can either use <code>@font-face</code> CSS code in your Critical CSS or stylesheet file or, when using Font Face API optimization, you can add a JSON config object in the Font Face API configuration.</p>

<div class="suboption">
    <button type="button" class="button" id="display_fontcss">Display @font-face CSS</button>
    <button type="button" class="button" id="display_fontface">Display Font Face API Config</button>
    <span class="spinner" style="float:none;"></span>
</div>

<div class="suboption" id="fontface-config" data-ns="fonts.fontface" style="display:none;">

    Font Source: <select id="fontface_source" data-theme-url="<?php print esc_attr($themepath); ?>" data-cdn-url="<?php print esc_attr($cdnurl); ?>">
        <option value="local" selected>Local path (<?php print get_template(); ?>/fonts/)</option>
        <option value="cdn">Custom Font CDN (see optimization settings)</option>
        <option value="google">Google CDN</option>
    </select>

    <div class="suboption">
        <div id="fonts-fontface-config"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'o10n'); ?></div></div>
        <input type="hidden" class="json" name="o10n[fonts.fontface.config]" data-json-type="json-array" data-json-editor-height="auto" data-json-editor-init="1" />
        <input type="checkbox" name="o10n[fonts.fontface.enabled]" checked="checked" value="1" style="display:none;" />
        <p class="description">Copy and paste the applicable config objects to the Font Face API configuration in the <a href="<?php print add_query_arg(array( 'page' => 'o10n-fonts', 'tab' => 'optimization' ), admin_url('admin.php')); ?>">Font Optimization settings</a>.</p>
    </div>
</div>

<div id="fontcss-config" style="display:none;">

    <div class="suboption">
        Font Source: <select id="fontcss_source" data-theme-url="<?php print esc_attr($themepath); ?>" data-cdn-url="<?php print esc_attr($cdnurl); ?>">
            <option value="local" selected>Local path (<?php print get_template(); ?>/fonts/)</option>
            <option value="cdn">Custom Font CDN (see optimization settings)</option>
            <option value="google">Google CDN</option>
        </select>
        <label style="margin-left:10px;"><input type="checkbox" id="fontcss_legacy" value="1" checked="checked"> Include fonts for old browsers</label>
    </div>

    <div class="suboption">
        <textarea class="css" id="fontcss-css"></textarea>
    </div>
</div>

        </td>
    </tr>
</table>

<?php

// print form header
$this->form_end();
