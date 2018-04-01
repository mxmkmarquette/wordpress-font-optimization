=== Web Font Optimization ===
Contributors: o10n
Donate link: https://github.com/o10n-x/
Tags: font, webfont, font face api, optimization, google font loader, css, page speed, performance, speed, fonts, webfonts
Requires at least: 4.0
Requires PHP: 5.4
Tested up to: 4.9.4
Stable tag: 0.0.39
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Advanced Web Font optimization toolkit. Font Face API, Web Font Observer, Google Font Loader, Critical CSS, HTTP/2 Server Push, async and timed font rendering and more.

== Description ==

Advanced Web Font optimization toolkit. Font Face API, Web Font Observer, Google Font Loader, Critical CSS, HTTP/2 Server Push, async and timed font rendering and more.

The plugin provides a management solution for the following font loading technologies:

* [Font Face API](https://developer.mozilla.org/nl/docs/Web/API/FontFace)
* [Font Face Observer](https://fontfaceobserver.com/)
* [Google Font Loader](https://developers.google.com/fonts/docs/webfont_loader)

The plugin contains many unique innovations such as async and timed font loading and/or rendering which enables to load and/or render fonts only on specific screen sizes/devices using a [Media Query](https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries), when an element scrolls into view or using methods for page load time optimization purposes (`requestAnimationFrame` with frame targeting and more). Timed font loading is available for all loading strategies. 

With debug modus enabled, the browser console will show detailed information about the font loading and rendering process including a [Performance API](https://developer.mozilla.org/nl/docs/Web/API/Performance) result for an insight in the font loading performance of any given configuration.

The plugin contains a tool to download and install Google fonts locally for a theme, it provides an option to push fonts using HTTP/2 Server Push, it enables to remove linked fonts from HTML and CSS source code (`<link rel="stylesheet">` and `@import` links) and to remove Google Font Loader from HTML and javascript source code.

Additional features can be requested on the [Github forum](https://github.com/o10n-x/wordpress-font-optimization/issues).

**This plugin is a beta release.**

Documentation is available on [Github](https://github.com/o10n-x/wordpress-font-optimization/tree/master/docs).

== Installation ==

### WordPress plugin installation

1. Upload the `web-font-optimization/` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the plugin settings page.
4. Configure Web Font Optimization settings. Documentation is available on [Github](https://github.com/o10n-x/wordpress-font-optimization/tree/master/docs).

== Screenshots ==

1. Font Face API Optimization
2. Font Face Observer Optimization
3. Google Font Loader Optimization
4. Google Font Downloader
5. Google Font Theme Installation

== Changelog ==

= 0.0.39 =
* Added: plugin update protection (plugin index).

= 0.0.38 =
* Core update (see changelog.txt)

= 0.0.30 =
* Added: JSON profile editor for all optimization modules.

= 0.0.29 =
* Added: footer font load position (before `domready`) (AJ @ [WpFASTER.org](https://www.wpfaster.org/))
* Improved: plugin related admin scripts are now loaded using `wp_add_inline_script`.

= 0.0.28 =
Core update (see changelog.txt)

= 0.0.27 =
* Added: JSON profile editor (backup and restore plugin config)

= 0.0.26 =
Core update (see changelog.txt)

= 0.0.18 =
* Added: documentation links.

= 0.0.17 =
* Bugfix: uninstaller.

= 0.0.16 =
Core update (see changelog.txt)

= 0.0.15 =
Bugfix: settings link on plugin index.

= 0.0.14 =
Added: Improved Critical CSS management.

= 0.0.12 =
Bugfix: Font Face Observer not working.

= 0.0.11 =
Core update (see changelog.txt)

= 0.0.2 =
Added: unrender Font Face API fonts on Media Query change (timed render).

= 0.0.1 =
Beta release. Please provide feedback on [Github forum](https://github.com/o10n-x/wordpress-font-optimization/issues).

== Upgrade Notice ==

None.