[![Build Status](https://travis-ci.org/o10n-x/wordpress-font-optimization.svg?branch=master)](https://travis-ci.org/o10n-x/wordpress-font-optimization)

# Web Font Optimization

Advanced Web Font optimization toolkit. Font Face API, Web Font Observer, Google Font Loader, Critical CSS, async and timed font rendering and more.

* [Documentation](https://github.com/o10n-x/wordpress-font-optimization/tree/master/docs)
* [Description](https://github.com/o10n-x/wordpress-font-optimization#description)
* [Version history (Changelog)](https://github.com/o10n-x/wordpress-font-optimization/releases)

**This plugin is removed from WordPress.org. Read the story [here](https://github.com/o10n-x/wordpress-css-optimization/issues/4).**

## Installation

![Github Updater](https://github.com/afragen/github-updater/raw/develop/assets/GitHub_Updater_logo_small.png)

This plugin can be installed and updated using [Github Updater](https://github.com/afragen/github-updater) ([installation instructions](https://github.com/afragen/github-updater/wiki/Installation))

## WordPress WPO Collection

This plugin is part of a Website Performance Optimization collection that include [CSS](https://github.com/o10n-x/wordpress-css-optimization), [Javascript](https://github.com/o10n-x/wordpress-javascript-optimization), [HTML](https://github.com/o10n-x/wordpress-html-optimization), [HTTP/2](https://github.com/o10n-x/wordpress-http2-optimization), [Progressive Web App (Service Worker)](https://github.com/o10n-x/wordpress-pwa-optimization) and [Security Header](https://github.com/o10n-x/wordpress-security-header-optimization) optimization. 

The WPO optimization plugins provide in all essential tools that enable to achieve perfect [Google Lighthouse Test](https://developers.google.com/web/tools/lighthouse/) scores and to validate a website as [Google PWA](https://developers.google.com/web/progressive-web-apps/), an important ranking factor for Google's [Speed Update](https://searchengineland.com/google-speed-update-page-speed-will-become-ranking-factor-mobile-search-289904) (July 2018).

![Google Lighthouse Perfect Performance Scores](https://github.com/o10n-x/wordpress-css-optimization/blob/master/docs/images/google-lighthouse-pwa-validation.jpg)

The WPO optimization plugins are designed to work together with single plugin performance. The plugins provide the latest optimization technologies and many unique innovations.

### JSON shema configuration

The WPO optimization plugins are based on JSON schema based configuration that enables full control of the optimization using JSON. This provides several great advantages for website performance optimization.

Read more about [JSON schemas](https://github.com/o10n-x/wordpress-o10n-core/tree/master/schemas).

## Google PageSpeed vs Google Lighthouse Scores

While a Google PageSpeed 100 score is still of value, websites with a high Google PageSpeed score may score very bad in Google's new [Lighthouse performance test](https://developers.google.com/web/tools/lighthouse/). 

The following scores are for the same site. It shows that a perfect Google PageSpeed score does not correlate to a high Google Lighthouse performance score.

![Perfect Google PageSpeed 100 Score](https://github.com/o10n-x/wordpress-css-optimization/blob/master/docs/images/google-pagespeed-100.png) ![Google Lighthouse Critical Performance Score](https://github.com/o10n-x/wordpress-css-optimization/blob/master/docs/images/lighthouse-performance-15.png)

### Google PageSpeed score is outdated

For the open web to have a chance of survival in a mobile era it needs to compete with and win from native mobile apps. Google is dependent on the open web for it's advertising revenue. Google therefor seeks a way to secure the open web and the main objective is to rapidly enhance the quality of the open web to meet the standards of native mobile apps.

For SEO it is therefor simple: websites will need to meet the standards set by the [Google Lighthouse Test](https://developers.google.com/web/tools/lighthouse/) (or Google's future new tests). A website with perfect scores will be preferred in search over low performance websites. The officially announced [Google Speed Update](https://searchengineland.com/google-speed-update-page-speed-will-become-ranking-factor-mobile-search-289904) (July 2018) shows that Google is going as far as it can to drive people to enhance the quality to ultra high levels, to meet the quality of, and hopefully beat native mobile apps.

A perfect Google Lighthouse Score includes validation of a website as a [Progressive Web App (PWA)](https://developers.google.com/web/progressive-web-apps/).

Google offers another new website performance test that is much tougher than the Google PageSpeed score. It is based on a AI neural network and it can be accessed on https://testmysite.thinkwithgoogle.com

## Description

This plugin is a toolkit for professional web font optimization. 

The plugin provides an advanced management solution for the following font loading technologies:

* [Font Face API](https://developer.mozilla.org/nl/docs/Web/API/FontFace)
* [Font Face Observer](https://fontfaceobserver.com/)
* [Google Font Loader](https://developers.google.com/fonts/docs/webfont_loader)

The plugin contains many unique innovations such as async and timed font loading and/or rendering which enables to load and/or render fonts only on specific screen sizes/devices using a [Media Query](https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries), when an element scrolls into view or using methods for page load time optimization purposes (`requestAnimationFrame` with frame targeting and more). Timed font loading is available for all loading strategies. 

With debug modus enabled, the browser console will show detailed information about the font loading and rendering process including a [Performance API](https://developer.mozilla.org/nl/docs/Web/API/Performance) result for an insight in the font loading performance of any given configuration.

The plugin contains a tool to download and install Google fonts locally for a theme, it provides an option to push fonts using HTTP/2 Server Push, it enables to remove linked fonts from HTML and CSS source code (`<link rel="stylesheet">` and `@import` links) and to remove Google Font Loader from HTML and javascript source code.

Additional features can be requested on the [Github forum](https://github.com/o10n-x/wordpress-font-optimization/issues).
