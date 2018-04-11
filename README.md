[![Build Status](https://travis-ci.org/o10n-x/wordpress-font-optimization.svg?branch=master)](https://travis-ci.org/o10n-x/wordpress-font-optimization)

# Web Font Optimization

Advanced Web Font optimization toolkit. Font Face API, Web Font Observer, Google Font Loader, Critical CSS, async and timed font rendering and more.

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

The WPO optimization plugins are based on JSON schema configuration that enables full control of the optimization using a simple JSON document. This provides several great advantages for website performance optimization.

#### Advantage 1: platform independent

The WPO plugins are not like most other WordPress plugins. The plugins are purely focused on optimization technologies instead of controlling / modifying WordPress. This makes the the underlaying optimization technologies platform independent. The same technologies and configuration can be used on Magento, a Microsoft .NET CMS or a Node.js based CMS. 

#### Advantage 2: saving time

The JSON configuration enables much quicker tuning for experts and it enables to quickly copy and paste a proven configuration to a new website.

#### Advantage 3: public optimization knowledge and help

The JSON configuration can be easily shared and discussed on forums, enabling to build public knowledge about the options. Because the optimization configuration is independent from WordPress, the knowledge will be valid for any platform which increases the value, making it more likely to be able to receive free help.

#### Advantage 4: a basis for Artificial Intelligence

The JSON configuration concept, when completed, enables fine grained tuning of the optimization, not just on a per page level but even per individual visitor or based on the environment. This will enable to optimize the performance based on the [save-data](https://developers.google.com/web/updates/2016/02/save-data) header or for example to change an individual Web Font optimization setting specifically for iPhone 5 devices. 

While the JSON shema concept makes it more easy for human editors to perform such complex environment based optimization, it also provides a basis for a future AI to take full control over the optimization, enabling to achieve the absolute best website performance result for each individual user automatically.

While the AI may one day supplement or take over, experts will have a clear view of what the AI is doing (it produces simple JSON that is used by humans) and will be able to override at any point.

**This plugin is a beta release.**

Documentation is available [here](https://github.com/o10n-x/wordpress-font-optimization/tree/master/docs).

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
