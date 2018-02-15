# Web Font Optimization

Advanced Web Font optimization toolkit. Font Face API, Web Font Observer, Google Font Loader, Critical CSS, async and timed font rendering and more.

**This plugin is a beta release.**

Documentation is available [here](https://github.com/o10n-x/font-optimization/tree/master/docs).

## Description

This plugin is a toolkit for professional web font optimization. 

The plugin provides an advanced management solution for the following font loading technologies:

* [Font Face API](https://developer.mozilla.org/nl/docs/Web/API/FontFace)
* [Font Face Observer](https://fontfaceobserver.com/)
* [Google Font Loader](https://developers.google.com/fonts/docs/webfont_loader)

The plugin contains many unique innovations such as async and timed font loading and/or rendering which enables to load and/or render fonts only on specific screen sizes/devices using a [Media Query](https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries), when an element scrolls into view or using methods for page load time optimization purposes (`requestAnimationFrame` with frame targeting and more). Timed font loading is available for all loading strategies. 

With debug modus enabled, the browser console will show detailed information about the font loading and rendering process including a [Performance API](https://developer.mozilla.org/nl/docs/Web/API/Performance) result for an insight in the font loading performance of any given configuration.

The plugin contains a tool to download and install Google fonts locally for a theme, it provides an option to push fonts using HTTP/2 Server Push, it enables to remove linked fonts from HTML and CSS source code (`<link rel="stylesheet">` and `@import` links) and to remove Google Font Loader from HTML and javascript source code.

Additional features can be requested on the [Github forum](https://github.com/o10n-x/font-optimization/issues).

## Changelog

#### 0.0.1

Beta release. Please provide feedback on [Github forum](https://github.com/o10n-x/font-optimization/issues).

## Upgrade Notice

None.
