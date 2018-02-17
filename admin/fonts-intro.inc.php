<?php
/**
 * Intro admin template
 *
 * @package    optimization
 * @subpackage optimization/admin
 * @author     Optimization.Team <info@optimization.team>
 */

if (!defined('ABSPATH')) {
    exit;
}

$module_name = $view->module->name();
$module_version = $view->module->version();

?>
<div class="wrap">

	<div class="metabox-prefs">
		<div class="wrap about-wrap" style="position:relative;">
			<div style="float:right;">
				<a href="https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/webfont-optimization" target="_blank" rel="noopener"><img src="<?php print $view->module->dir_url(); ?>admin/images/google-fonts-logo-180.png" alt="Web Font Optimization" width="180" height="180" border="0" style="float:right;border:1px solid #e5e5e5;border-radius:2px;"></a>
			</div>
			<h1><?php print $module_name;?> <?php print $module_version;?></h1>

			<p class="about-text" style="min-height:inherit;">Thank you for using the <?php print $module_name;?> plugin by <a href="https://optimization.team/" target="_blank" rel="noopener" style="color:black;text-decoration:none;">Optimization.Team</a></p>
			
			<p class="about-text" style="min-height:inherit;">This plugin is a toolkit for advanced Web Font optimization for WordPress. <!--The plugin can be used stand alone or as a module for the <a href="#">Performance Optimization plugin</a>.--></p>

			<p class="about-text info_yellow" style="min-height:inherit;"><strong>Warning:</strong> This plugin is intended for optimization professionals and advanced WordPress users.</p>

			<p class="about-text" style="min-height:inherit;">Getting started? Read <a href="https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/webfont-optimization" target="_blank">this article</a> about Web Font Optimization by Google.</p>

			<p class="about-text" style="min-height:inherit;">If you are happy with the plugin, please consider to <a href="https://wordpress.org/support/plugin/web-font-optimization/reviews/#new-post" target="_blank" rel="noopener">write a review</a> and <span class="star" style="display:inline-block;vertical-align:middle;"><a class="github-button" data-manual="1" data-size="large" href="https://github.com/o10n-x/wordpress-font-optimization" data-icon="octicon-star" data-show-count="true" aria-label="Star o10n-x/font-optimization on GitHub">Star</a></span> on Github.</p>
			</div>

		</div>
	</div>

</div>