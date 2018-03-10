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

// print form header
$this->form_start(__('Web Font Optimization', 'o10n'), 'fonts');

$critical_css_files = $this->options->get('css.critical.files');
$themedir = $this->file->theme_directory(array('critical-css'));
if (is_array($critical_css_files) && isset($critical_css_files['webfonts.css'])) {
    $critical_css_exists = file_exists($themedir . 'webfonts.css');
} else {
    $critical_css_exists = false;
}

?>

<table class="form-table">
    <tr valign="top">
        <th scope="row">Font Face API</th>
        <td>            
            <label><input type="checkbox" name="o10n[fonts.fontface.enabled]" data-json-ns="1" value="1"<?php $checked('fonts.fontface.enabled'); ?>> Enable
</label>
            <p class="description" style="margin-bottom:1em;">Optimize font loading using <code>Font Face API</code>.</p>

            <div class="suboption" data-ns="fonts.fontface"<?php $visible('fonts.fontface'); ?>>
                <h5 class="h">&nbsp;Font Face API Config (<a href="https://github.com/o10n-x/wordpress-font-optimization/tree/master/docs#font-face-api-configuration" target="_blank">documentation</a>)</h5>
                <div id="fonts-fontface-config"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'o10n'); ?></div></div>
                <input type="hidden" class="json" name="o10n[fonts.fontface.config]" data-json-type="json-array" data-json-editor-height="auto" data-json-editor-init="1" value="<?php print esc_attr($json('fonts.fontface.config')); ?>" />
			</div>

			<div class="suboption" data-ns="fonts.fontface"<?php $visible('fonts.fontface'); ?>>
	            <label><input type="checkbox" name="o10n[fonts.fontface.rel_preload]" data-json-ns="1" value="1"<?php $checked('fonts.fontface.rel_preload'); ?>> rel="preload"</label>
	            <p class="description" style="margin-bottom:1em;">Include <code>&lt;link rel="preload" as="font" /&gt;</code> for WOFF2 fonts.</p>

				<div class="suboption" data-ns="fonts.fontface"<?php $visible('fonts.fontface', (($get('fonts.fontface.render_timing.enabled') === false || $get('fonts.fontface.render_timing.type') !== 'requestAnimationFrame')) && $get('fonts.fontface.load_position') !== 'timing');  ?> data-ns-condition="fonts.fontface.render_timing.enabled==false||fonts.fontface.render_timing.type!=requestAnimationFrame&&fonts.fontface.load_position!=timing">
					<label><input type="checkbox" name="o10n[fonts.fontface.requestAnimationFrame]" data-json-ns="1" value="1"<?php $checked('fonts.fontface.requestAnimationFrame'); ?>> requestAnimationFrame</label>
	            	<p class="description" style="margin-bottom:1em;">Render fonts using <code>requestAnimationFrame</code>.</p>
				</div>
	           
				<div class="suboption">
					<h5 class="h">&nbsp;Load Position</h5>
		            <select name="o10n[fonts.fontface.load_position]" data-ns-change="fonts.fontface">
		                <option value="header"<?php $selected('fonts.fontface.load_position', 'header'); ?>>Header</option>
		                <option value="timing"<?php $selected('fonts.fontface.load_position', 'timing'); ?>>Timed</option>
		            </select>
		            <p class="description">Select the position of the HTML document where the loading of fonts will start.</p>
	            </div>
	            <div class="suboption" data-ns="fonts.fontface""<?php $visible('fonts.fontface', ($get('fonts.fontface.load_position') === 'timing'));  ?> data-ns-condition="fonts.fontface.load_position==timing">
		            <h5 class="h">&nbsp;Load Timing Method</h5>
	                <select name="o10n[fonts.fontface.load_timing.type]" data-ns-change="fonts.fontface" data-json-default="<?php print esc_attr(json_encode('domReady')); ?>">
	                    <option value="domReady"<?php $selected('fonts.fontface.load_timing.type', 'domReady'); ?>>domReady</option>
	                    <option value="requestAnimationFrame"<?php $selected('fonts.fontface.load_timing.type', 'requestAnimationFrame'); ?>>requestAnimationFrame (on paint)</option>
	                    <option value="inview"<?php $selected('fonts.fontface.load_timing.type', 'inview'); ?>>element in view (on scroll)</option>
	                    <option value="media"<?php $selected('fonts.fontface.load_timing.type', 'media'); ?>>responsive (Media Query)</option>
	                </select>
	                <p class="description">Select the timing method for async font loading. This option is also available per individual font in the Font Face API config.</p>

	                <div class="suboption" data-ns="fonts.fontface"<?php $visible('fonts.fontface', ($get('fonts.fontface.load_timing.type') === 'requestAnimationFrame'));  ?> data-ns-condition="fonts.fontface.load_timing.type==requestAnimationFrame">
			            <h5 class="h">&nbsp;Frame number</h5>
			            <input type="number" style="width:60px;" min="1" name="o10n[fonts.fontface.load_timing.frame]" value="<?php $value('fonts.fontface.load_timing.frame'); ?>" />
			            <p class="description">Optionally, select the frame number to start loading fonts. <code>requestAnimationFrame</code> will be called this many times before the fonts are loaded.</p>
		            </div>

		            <div class="suboption" data-ns="fonts.fontface"<?php $visible('fonts.fontface', ($get('fonts.fontface.load_timing.type') === 'inview'));  ?> data-ns-condition="fonts.fontface.load_timing.type==inview">
		            	<p class="poweredby">Powered by <a href="https://github.com/camwiegert/in-view" target="_blank">in-view.js</a><span class="star"><a class="github-button" data-manual="1" href="https://github.com/camwiegert/in-view" data-icon="octicon-star" data-show-count="true" aria-label="Star camwiegert/in-view on GitHub">Star</a></span></p>
			            <h5 class="h">&nbsp;CSS selector</h5>
			            <input type="text" name="o10n[fonts.fontface.load_timing.selector]" value="<?php $value('fonts.fontface.load_timing.selector'); ?>" />
			            <p class="description">Enter the <a href="https://developer.mozilla.org/en-US/docs/Web/API/Document/querySelector" target="_blank">CSS selector</a> of the element to watch.</p>
			            
			            <div class="suboption">
				            <h5 class="h">&nbsp;Offset</h5>
				            <input type="number" style="width:60px;" name="o10n[fonts.fontface.load_timing.offset]" value="<?php $value('fonts.fontface.load_timing.offset'); ?>" />
				            <p class="description">Optionally, enter an offset from the edge of the element to start font loading.</p>
			            </div>
		            </div>

		            <div class="suboption" data-ns="fonts.fontface"<?php $visible('fonts.fontface', ($get('fonts.fontface.load_timing.type') === 'media'));  ?> data-ns-condition="fonts.fontface.load_timing.type==media">
			            <h5 class="h">&nbsp;Media Query</h5>
			            <input type="text" name="o10n[fonts.fontface.load_timing.media]" value="<?php $value('fonts.fontface.load_timing.media'); ?>" style="width:400px;max-width:100%;" />
			            <p class="description">Enter a <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries" target="_blank">Media Query</a> for conditional font loading, e.g. omit fonts on mobile devices.</p>
		            </div>
                </div>

	            <div class="suboption" data-ns="fonts.fontface""<?php $visible('fonts.fontface', ($get('fonts.fontface.load_position') !== 'timing'));  ?> data-ns-condition="fonts.fontface.load_position!=timing">
					<h5 class="h">&nbsp;Timed Render</h5>
		            
		            <label><input type="checkbox" name="o10n[fonts.fontface.render_timing.enabled]" data-json-ns="1" data-ns-change="fonts.fontface" value="1"<?php $checked('fonts.fontface.render_timing.enabled'); ?> /> Enabled</label>
		            <p class="description">When enabled, fonts are rendered asynchronously using a timing method.</p>

		            <div class="suboption" data-ns="fonts.fontface.render_timing"<?php $visible('fonts.fontface.render_timing'); ?>>
			            <h5 class="h">&nbsp;Render Timing Method</h5>
		                <select name="o10n[fonts.fontface.render_timing.type]" data-ns-change="fonts.fontface" data-json-default="<?php print esc_attr(json_encode('domReady')); ?>">
		                    <option value="domReady"<?php $selected('fonts.fontface.render_timing.type', 'domReady'); ?>>domReady</option>
		                    <option value="requestAnimationFrame"<?php $selected('fonts.fontface.render_timing.type', 'requestAnimationFrame'); ?>>requestAnimationFrame (on paint)</option>
		                    <option value="inview"<?php $selected('fonts.fontface.render_timing.type', 'inview'); ?>>element in view (on scroll)</option>
		                    <option value="media"<?php $selected('fonts.fontface.render_timing.type', 'media'); ?>>responsive (Media Query)</option>
		                </select>
		                <p class="description">Select the timing method for async font rendering. This option is also available per individual font in the Font Face API config.</p>

		                <div class="suboption" data-ns="fonts.fontface.render_timing"<?php $visible('fonts.fontface.render_timing', ($get('fonts.fontface.render_timing.type') === 'requestAnimationFrame'));  ?> data-ns-condition="fonts.fontface.render_timing.type==requestAnimationFrame">
				            <h5 class="h">&nbsp;Frame number</h5>
				            <input type="number" style="width:60px;" min="1" name="o10n[fonts.fontface.render_timing.frame]" value="<?php $value('fonts.fontface.render_timing.frame'); ?>" />
				            <p class="description">Optionally, select the frame number to start font rendering. <code>requestAnimationFrame</code> will be called this many times before the fonts are rendered.</p>
			            </div>

			            <div class="suboption" data-ns="fonts.fontface.render_timing"<?php $visible('fonts.fontface.render_timing', ($get('fonts.fontface.render_timing.type') === 'inview'));  ?> data-ns-condition="fonts.fontface.render_timing.type==inview">
			            	<p class="poweredby">Powered by <a href="https://github.com/camwiegert/in-view" target="_blank">in-view.js</a><span class="star"><a class="github-button" data-manual="1" href="https://github.com/camwiegert/in-view" data-icon="octicon-star" data-show-count="true" aria-label="Star camwiegert/in-view on GitHub">Star</a></span></p>
				            <h5 class="h">&nbsp;CSS selector</h5>
				            <input type="text" name="o10n[fonts.fontface.render_timing.selector]" value="<?php $value('fonts.fontface.render_timing.selector'); ?>" />
				            <p class="description">Enter the <a href="https://developer.mozilla.org/en-US/docs/Web/API/Document/querySelector" target="_blank">CSS selector</a> of the element to watch.</p>
				            
				            <div class="suboption">
					            <h5 class="h">&nbsp;Offset</h5>
					            <input type="number" style="width:60px;" name="o10n[fonts.fontface.render_timing.offset]" value="<?php $value('fonts.fontface.render_timing.offset'); ?>" />
					            <p class="description">Optionally, enter an offset from the edge of the element to start font rendering.</p>
				            </div>
			            </div>

			            <div class="suboption" data-ns="fonts.fontface.render_timing"<?php $visible('fonts.fontface.render_timing', ($get('fonts.fontface.render_timing.type') === 'media'));  ?> data-ns-condition="fonts.fontface.render_timing.type==media">
				            <h5 class="h">&nbsp;Media Query</h5>
				            <input type="text" name="o10n[fonts.fontface.render_timing.media]" value="<?php $value('fonts.fontface.render_timing.media'); ?>" style="width:400px;max-width:100%;" />
				            <p class="description">Enter a <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries" target="_blank">Media Query</a> for conditional font rendering, e.g. render a font on mobile device orientation change.</p>
			            </div>

	                </div>
	            </div>
            </div>
        </td>
    </tr>
    <tr valign="top" data-ns="fonts.fontface"<?php $visible('fonts.fontface'); ?>>
        <th scope="row">HTTP/2 Server Push</th>
        <td>
        <?php if (!$module_loaded('http2')) {
    ?>
<p class="description">Install the <a href="<?php print esc_url(add_query_arg(array('s' => 'o10n', 'tab' => 'search', 'type' => 'author'), admin_url('plugin-install.php'))); ?>">HTTP/2 Optimization</a> plugin to use this feature.</p>
<?php
} else {
        ?>
            <label><input type="checkbox" name="o10n[fonts.http2_push.enabled]" data-json-ns="1" value="1"<?php $checked('fonts.http2_push.enabled'); ?> /> Enabled</label>
            <p class="description">When enabled, Font Face API loaded WOFF2 fonts are automatically pushed using <a href="https://developers.google.com/web/fundamentals/performance/http2/#server_push" target="_blank">HTTP/2 Server Push</a>.</p>

            <?php
                if (!$this->env->is_ssl()) {
                    ?>
<p class="warning_red" style="padding:5px;padding-left:7px;" data-ns="fonts.http2_push"<?php $visible('fonts.http2_push'); ?>>HTTP/2 requires a SSL connection.</p>
<?php
                } ?>
            <p class="info_yellow" data-ns="fonts.http2_push"<?php $visible('fonts.http2_push'); ?>>You can enable or disable HTTP/2 Server Push for individual fonts in the Font Face API config. To push fonts manually, use the <a href="<?php print add_query_arg(array(
                'page' => 'o10n-http2', 'tab' => 'push'
            ), admin_url('admin.php')); ?>">HTTP/2 Server Push configuration</a>.</p>
<?php
    }
?>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Font Face Observer</th>
        <td>
        	<p class="poweredby">Powered by <a href="https://fontfaceobserver.com/" target="_blank">Font Face Observer</a><span class="star"><a class="github-button" data-manual="1" href="https://github.com/bramstein/fontfaceobserver" data-icon="octicon-star" data-show-count="true" aria-label="Star bramstein/fontfaceobserver on GitHub">Star</a></span></p>
            <label><input type="checkbox" name="o10n[fonts.fontfaceobserver.enabled]" data-json-ns="1" value="1"<?php $checked('fonts.fontfaceobserver.enabled'); ?>> Enable
</label>
            <p class="description" style="margin-bottom:1em;">Optimize font loading using <a href="https://fontfaceobserver.com/" target="_blank" rel="noopener">Font Face Ob­server</a>. Supports all fonts, including local hosted fonts, <a href="http://www.google.com/fonts" target="_blank" rel="noopener">Google Fonts</a>, <a href="http://typekit.com/" target="_blank" rel="noopener">Typekit</a>, <a href="https://fonts.com/" target="_blank" rel="noopener">Fonts.com</a>, and <a href="http://webtype.com/" target="_blank" rel="noopener">Webtype</a>.</p>

            <div class="suboption" data-ns="fonts.fontfaceobserver"<?php $visible('fonts.fontfaceobserver'); ?>>
                <h5 class="h">&nbsp;Font Face Observer Config (<a href="https://github.com/o10n-x/wordpress-font-optimization/tree/master/docs#font-face-observer-configuration" target="_blank">documentation</a>)</h5>
                <div id="fonts-fontfaceobserver-config"><div class="loading-json-editor"><?php print __('Loading JSON editor...', 'o10n'); ?></div></div>
                <input type="hidden" class="json" name="o10n[fonts.fontfaceobserver.config]" data-json-type="json-array" data-json-editor-height="auto" data-json-editor-init="1" value="<?php print esc_attr($json('fonts.fontfaceobserver.config')); ?>" />
            </div>

            <div class="suboption" data-ns="fonts.fontfaceobserver"<?php $visible('fonts.fontfaceobserver'); ?>>
				<h5 class="h">&nbsp;Load Position</h5>
	            <select name="o10n[fonts.fontfaceobserver.load_position]" data-ns-change="fonts.fontfaceobserver">
	                <option value="header"<?php $selected('fonts.fontfaceobserver.load_position', 'header'); ?>>Header</option>
	                <option value="timing"<?php $selected('fonts.fontfaceobserver.load_position', 'timing'); ?>>Timed</option>
	            </select>
	            <p class="description">Select the position of the HTML document where the loading of fonts will start.</p>
            </div>
            <div class="suboption" data-ns="fonts.fontfaceobserver""<?php $visible('fonts.fontfaceobserver', ($get('fonts.fontfaceobserver.load_position') === 'timing'));  ?> data-ns-condition="fonts.fontfaceobserver.load_position==timing">
	            <h5 class="h">&nbsp;Load Timing Method</h5>
                <select name="o10n[fonts.fontfaceobserver.load_timing.type]" data-ns-change="fonts.fontfaceobserver" data-json-default="<?php print esc_attr(json_encode('domReady')); ?>">
                    <option value="domReady"<?php $selected('fonts.fontfaceobserver.load_timing.type', 'domReady'); ?>>domReady</option>
                    <option value="requestAnimationFrame"<?php $selected('fonts.fontfaceobserver.load_timing.type', 'requestAnimationFrame'); ?>>requestAnimationFrame (on paint)</option>
                    <option value="inview"<?php $selected('fonts.fontfaceobserver.load_timing.type', 'inview'); ?>>element in view (on scroll)</option>
                    <option value="media"<?php $selected('fonts.fontfaceobserver.load_timing.type', 'media'); ?>>responsive (Media Query)</option>
                </select>
                <p class="description">Select the timing method for async font loading. This option is also available per individual font in the Font Face API config.</p>

                <div class="suboption" data-ns="fonts.fontfaceobserver"<?php $visible('fonts.fontfaceobserver', ($get('fonts.fontfaceobserver.load_timing.type') === 'requestAnimationFrame'));  ?> data-ns-condition="fonts.fontfaceobserver.load_timing.type==requestAnimationFrame">
		            <h5 class="h">&nbsp;Frame number</h5>
		            <input type="number" style="width:60px;" min="1" name="o10n[fonts.fontfaceobserver.load_timing.frame]" value="<?php $value('fonts.fontfaceobserver.load_timing.frame'); ?>" />
		            <p class="description">Optionally, select the frame number to start loading fonts. <code>requestAnimationFrame</code> will be called this many times before the fonts are loaded.</p>
	            </div>

	            <div class="suboption" data-ns="fonts.fontfaceobserver"<?php $visible('fonts.fontfaceobserver', ($get('fonts.fontfaceobserver.load_timing.type') === 'inview'));  ?> data-ns-condition="fonts.fontfaceobserver.load_timing.type==inview">
		            <h5 class="h">&nbsp;CSS selector</h5>
		            <input type="text" name="o10n[fonts.fontfaceobserver.load_timing.selector]" value="<?php $value('fonts.fontfaceobserver.load_timing.selector'); ?>" />
		            <p class="description">Enter the <a href="https://developer.mozilla.org/en-US/docs/Web/API/Document/querySelector" target="_blank">CSS selector</a> of the element to watch.</p>
		            
		            <div class="suboption">
			            <h5 class="h">&nbsp;Offset</h5>
			            <input type="number" style="width:60px;" name="o10n[fonts.fontfaceobserver.load_timing.offset]" value="<?php $value('fonts.fontfaceobserver.load_timing.offset'); ?>" />
			            <p class="description">Optionally, enter an offset from the edge of the element to start font loading.</p>
		            </div>
	            </div>

	            <div class="suboption" data-ns="fonts.fontfaceobserver"<?php $visible('fonts.fontfaceobserver', ($get('fonts.fontfaceobserver.load_timing.type') === 'media'));  ?> data-ns-condition="fonts.fontfaceobserver.load_timing.type==media">
		            <h5 class="h">&nbsp;Media Query</h5>
		            <input type="text" name="o10n[fonts.fontfaceobserver.load_timing.media]" value="<?php $value('fonts.fontfaceobserver.load_timing.media'); ?>" style="width:400px;max-width:100%;" />
		            <p class="description">Enter a <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries" target="_blank">Media Query</a> for conditional font loading, e.g. omit fonts on mobile devices.</p>
	            </div>
       		</div>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Google Font Loader</th>
        <td>
        	<p class="poweredby">Powered by <a href="https://developers.google.com/fonts/docs/webfont_loader" target="_blank">Web Font Loader</a><span class="star"><a class="github-button" data-manual="1" href="https://github.com/typekit/webfontloader" data-icon="octicon-star" data-show-count="true" aria-label="Star typekit/webfontloader on GitHub">Star</a></span></p>
            <label><input type="checkbox" name="o10n[fonts.googlefontloader.enabled]" data-json-ns="1" value="1"<?php $checked('fonts.googlefontloader.enabled'); ?>> Enable
</label>
            <p class="description" style="margin-bottom:1em;">Enable the <a href="https://github.com/typekit/webfontloader" target="_blank" rel="noopener">Google Web Font Loader</a> javascript library.</p>

            <div class="suboption" data-ns="fonts.googlefontloader"<?php $visible('fonts.googlefontloader'); ?>>
            <h5 class="h">&nbsp;WebFontConfig (<a href="https://github.com/o10n-x/wordpress-font-optimization/tree/master/docs#google-web-font-loader-configuration" target="_blank">documentation</a>)</h5>
            <textarea class="json-array-lines" id="fonts_webfontconfig_editor" name="o10n[fonts.googlefontloader.config]" placeholder="<?php print esc_attr('WebFontConfig = { 
    classes: false, 
    typekit: { id: \'xxxxxx\' }, 
    loading: function() {}, 
    google: { 
        families: [\'Droid Sans\', \'Droid Serif\'] 
    }
};'); ?>"><?php $config = $get('fonts.googlefontloader.config'); if ($config && substr($config, 0, 1) === '{') {
    print 'WebFontConfig = ';
} print $config; ?></textarea>
            <p class="description">Enter the <code>WebFontConfig</code> variable. (<a href="https://github.com/typekit/webfontloader#configuration">documentation</a>)</p>
<?php
    if ($config && substr($config, 0, 1) !== '{') {
        print '<p class="warning_red">Failed to recognize WebFontConfig variable. The correct format is <code>WebFontConfig = { ... }</code>.</p>';
    }
?>
            </div>
            <div class="suboption">
            	<label><input type="checkbox" name="o10n[fonts.googlefontloader.remove]" data-json-ns="1" value="1"<?php $checked('fonts.googlefontloader.remove'); ?>> Remove existing Google Font Loader</label>
            <p class="description" style="margin-bottom:1em;">This option filters out existing WebFontConfig variables and webfont.js scripts from both HTML and javascript.</p>
            <?php if (!$module_loaded('js')) {
    ?>
<p class="description">Install the <a href="<?php print esc_url(add_query_arg(array('s' => 'o10n', 'tab' => 'search', 'type' => 'author'), admin_url('plugin-install.php'))); ?>">Javascript Optimization</a> plugin to remove the font loader from javascript file sources.</p>
<?php
} else {
        ?>
           <?php
    }
       ?>
            </div>
            <div class="suboption" data-ns="fonts.googlefontloader"<?php $visible('fonts.googlefontloader'); ?>>
				<h5 class="h">&nbsp;Load Position</h5>
	            <select name="o10n[fonts.googlefontloader.load_position]" data-ns-change="fonts.googlefontloader">
	                <option value="header"<?php $selected('fonts.googlefontloader.load_position', 'header'); ?>>Header</option>
	                <option value="timing"<?php $selected('fonts.googlefontloader.load_position', 'timing'); ?>>Timed</option>
	            </select>
	            <p class="description">Select the position of the HTML document where the loading of fonts will start.</p>
            </div>
            <div class="suboption" data-ns="fonts.googlefontloader""<?php $visible('fonts.googlefontloader', ($get('fonts.googlefontloader.load_position') === 'timing'));  ?> data-ns-condition="fonts.googlefontloader.load_position==timing">
	            <h5 class="h">&nbsp;Load Timing Method</h5>
                <select name="o10n[fonts.googlefontloader.load_timing.type]" data-ns-change="fonts.googlefontloader" data-json-default="<?php print esc_attr(json_encode('domReady')); ?>">
                    <option value="domReady"<?php $selected('fonts.googlefontloader.load_timing.type', 'domReady'); ?>>domReady</option>
                    <option value="requestAnimationFrame"<?php $selected('fonts.googlefontloader.load_timing.type', 'requestAnimationFrame'); ?>>requestAnimationFrame (on paint)</option>
                    <option value="inview"<?php $selected('fonts.googlefontloader.load_timing.type', 'inview'); ?>>element in view (on scroll)</option>
                    <option value="media"<?php $selected('fonts.googlefontloader.load_timing.type', 'media'); ?>>responsive (Media Query)</option>
                </select>
                <p class="description">Select the timing method for async font loading. This option is also available per individual font in the Font Face API config.</p>

                <div class="suboption" data-ns="fonts.googlefontloader"<?php $visible('fonts.googlefontloader', ($get('fonts.googlefontloader.load_timing.type') === 'requestAnimationFrame'));  ?> data-ns-condition="fonts.googlefontloader.load_timing.type==requestAnimationFrame">
		            <h5 class="h">&nbsp;Frame number</h5>
		            <input type="number" style="width:60px;" min="1" name="o10n[fonts.googlefontloader.load_timing.frame]" value="<?php $value('fonts.googlefontloader.load_timing.frame'); ?>" />
		            <p class="description">Optionally, select the frame number to start loading fonts. <code>requestAnimationFrame</code> will be called this many times before the fonts are loaded.</p>
	            </div>

	            <div class="suboption" data-ns="fonts.googlefontloader"<?php $visible('fonts.googlefontloader', ($get('fonts.googlefontloader.load_timing.type') === 'inview'));  ?> data-ns-condition="fonts.googlefontloader.load_timing.type==inview">
		            <h5 class="h">&nbsp;CSS selector</h5>
		            <input type="text" name="o10n[fonts.googlefontloader.load_timing.selector]" value="<?php $value('fonts.googlefontloader.load_timing.selector'); ?>" />
		            <p class="description">Enter the <a href="https://developer.mozilla.org/en-US/docs/Web/API/Document/querySelector" target="_blank">CSS selector</a> of the element to watch.</p>
		            
		            <div class="suboption">
			            <h5 class="h">&nbsp;Offset</h5>
			            <input type="number" style="width:60px;" name="o10n[fonts.googlefontloader.load_timing.offset]" value="<?php $value('fonts.googlefontloader.load_timing.offset'); ?>" />
			            <p class="description">Optionally, enter an offset from the edge of the element to start font loading.</p>
		            </div>
	            </div>

	            <div class="suboption" data-ns="fonts.googlefontloader"<?php $visible('fonts.googlefontloader', ($get('fonts.googlefontloader.load_timing.type') === 'media'));  ?> data-ns-condition="fonts.googlefontloader.load_timing.type==media">
		            <h5 class="h">&nbsp;Media Query</h5>
		            <input type="text" name="o10n[fonts.googlefontloader.load_timing.media]" value="<?php $value('fonts.googlefontloader.load_timing.media'); ?>" style="width:400px;max-width:100%;" />
		            <p class="description">Enter a <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries" target="_blank">Media Query</a> for conditional font loading, e.g. omit fonts on mobile devices.</p>
	            </div>
       		</div>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Remove linked fonts</th>
        <td>
        	<label><input type="checkbox" name="o10n[fonts.remove_linked.enabled]" data-json-ns="1" value="1"<?php $checked('fonts.remove_linked.enabled'); ?> /> Enabled</label>
        	<p class="description">Remove font CSS links and <code>@import</code> from HTML and CSS.</p>
<?php if (!$module_loaded('css')) {
           ?>
<p class="description">Install the <a href="<?php print esc_url(add_query_arg(array('s' => 'o10n', 'tab' => 'search', 'type' => 'author'), admin_url('plugin-install.php'))); ?>">CSS Optimization</a> plugin to remove <code>@import</code> links from CSS.</p>
<?php
       }
?>
        	<div style="margin-top:0.5em;" data-ns="fonts.remove_linked"<?php $visible('fonts.remove_linked'); ?>>
                <label><input type="checkbox" value="1" name="o10n[fonts.remove_linked.filter.enabled]" data-json-ns="1"<?php $checked('fonts.remove_linked.filter.enabled'); ?> /> Enable filter</label>
                <p class="description" data-ns-hide="fonts.remove_linked.filter"<?php $invisible('fonts.remove_linked.filter'); ?>>The default filter applies to many known web fonts such as <code>fonts.googleapis.com</code>.</p>
            </div>


        </td>
    </tr>
    <tr valign="top" data-ns="fonts.remove_linked.filter"<?php $visible('fonts.remove_linked.filter'); ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;Linked Font CSS Removal List</h5>
            <textarea class="json-array-lines" name="o10n[fonts.remove_linked.filter.list]" data-json-type="json-array-lines" placeholder="Remove stylesheets on this list."><?php $line_array('fonts.remove_linked.filter.list'); ?></textarea>
            <p class="description">Enter (parts of) stylesheet URI's to remove, e.g. <code>fonts.googleapis.com</code>. One match string per line.</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Font CDN</th>
        <td>
            <label><input type="checkbox" value="1" name="o10n[fonts.cdn.enabled]" data-json-ns="1"<?php $checked('fonts.cdn.enabled'); ?> /> Enabled</label>
            <p class="description">When enabled, local Font Face API loaded fonts are loaded via a Content Delivery Network (CDN).</p>

            <div data-ns="fonts.cdn"<?php $visible('fonts.cdn'); ?>>
                <p data-ns="fonts.http2_push"<?php $visible('fonts.http2_push'); ?>>
                    <label><input type="checkbox" name="o10n[fonts.cdn.http2_push]" value="1"<?php $checked('fonts.cdn.http2_push'); ?> /> Apply CDN to HTTP/2 pushed fonts.</label>
                </p>
            </div>
        </td>
    </tr>
    <tr valign="top" data-ns="fonts.cdn"<?php $visible('fonts.cdn'); ?>>
        <th scope="row">&nbsp;</th>
        <td style="padding-top:0px;">
            <h5 class="h">&nbsp;CDN URL</h5>
            <input type="url" name="o10n[fonts.cdn.url]" value="<?php $value('fonts.cdn.url'); ?>" style="width:500px;max-width:100%;" placeholder="https://cdn.yourdomain.com/" />
            <p class="description">Enter a CDN URL for fonts, e.g. <code>https://fonts.domain.com/</code></p>
            <br />
            <h5 class="h">&nbsp;CDN Mask</h5>
            <input type="text" name="o10n[fonts.cdn.mask]" value="<?php $value('fonts.cdn.mask'); ?>" style="width:500px;max-width:100%;" placeholder="/" />
            <p class="description">Optionally, enter a CDN mask to apply to the font path, e.g. <code>/wp-content/themes/<?php print get_template(); ?>/fonts/</code> to access fonts from the root of the CDN domain. The CDN mask enables to shorten CDN URLs.</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Critical CSS</th>
        <td>
        <?php if (!$module_loaded('css')) {
    ?>
<p class="description">Install the <a href="<?php print esc_url(add_query_arg(array('s' => 'o10n', 'tab' => 'search', 'type' => 'author'), admin_url('plugin-install.php'))); ?>">CSS Optimization</a> plugin to use this feature.</p>
<?php
} else {
        ?>
        	<p class="description">To prevent a <a href="https://css-tricks.com/fout-foit-foft/" target="_blank">Flash of Unstyled Text</a> (FOUT, FOIT or FOFT) you can include the font CSS in the Critical CSS.</p>

<?php
        if ($critical_css_exists) {
            ?>
	<p class="suboption"><a href="<?php print esc_url(add_query_arg(array('page' => 'o10n-css-editor','file' => 'critical-css/webfonts.css'), admin_url('admin.php'))); ?>" class="button button-large">Edit <strong>critical-css/webfonts.css</strong></a></p>
<?php
        } else {
            ?>
        	<p class="info_yellow suboption"><code><?php print str_replace('/wp-content/themes/', '', $this->file->safe_path($themedir)); ?>webfonts.css</code> does not exist. <a href="<?php print esc_url(add_query_arg(array('page' => 'o10n-fonts','tab' => 'optimization', 'create-critical-css' => 1), admin_url('admin.php'))); ?>">Click here</a> to create it.</p>
<?php
        }
    }
?>
        </td>
    </tr>
    </table>


<p class="suboption info_yellow"><strong><span class="dashicons dashicons-lightbulb"></span></strong> You can enable debug modus by adding <code>define('O10N_DEBUG', true);</code> to wp-config.php. The browser console will show details about font loading and a <a href="https://developer.mozilla.org/nl/docs/Web/API/Performance" target="_blank" rel="noopener">Performance API</a> result for each step of the loading and rendering process.</p>

<hr />
<?php
    submit_button(__('Save'), 'primary large', 'is_submit', false);

// print form header
$this->form_end();
