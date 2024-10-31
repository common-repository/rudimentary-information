== Rudimentary Information ==
Contributors: benlumia007
Donate link: https://www.benjlu.com
Tags: api, plugins, themes
Requires at least: 4.8.3
Tested up to: 5.6.2
Stable tag: 0.1.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Rudimentary Information allows you to grab information specifically for an individual theme and plugin by using the WordPress Themes API and Plugins API.

== Description ==
Rudimentary Information allows you to grab information specifically for an individual theme or plugin by fetching data from the WordPress Themes API and Plugins API. This plugin is very simple yet easy to use by providing a valid slug and field to display a specific information. This plugin uses shortcode to display information.  

= Working with Themes =
To display a specific theme, please use the following shortcode

[theme-info slug='theme-slug' field='version']

The slug is the name of the theme that you want to display. The field is an array of choices that you can use to display a piece of information such as version or last_updated information.

The following fields that are currently supported are as follow:

* name
* homepage
* screenshot_url
* preview_url
* download_link
* downloaded
* last_updated
- version

= Working with Plugins =
To display a specific plugin, please use the following shortcode

[plugin-info slug='plugin-slug' field='version']

The slug is the name of the plugin that you want to display. The field is an array of choices that you can use to display a piece of information such as version or last_updated information.

The following fields that are currently supported are as follow:

* name
* homepage
* screenshot_url
* preview_url
* download_link
* downloaded
* last_updated
- version

Now supports Meta Box and Widget, an extra feature that you don't need to waste your time with shortcode but shortcodes will be avaiable if you need to grab small information from the API. For this new feature to work properly, you will need to set a theme slug or plugin slug in the Portfolio (Jetpack Portfolio) page. This will save the slug as part of the page and you can use the plugin info widget or theme info widget to display the specific theme. Displays Tags is also supported in its own Widget.

== Installation ==

1.0 - In your admin panel, go to Appearance -> Plugins and click the Add New button.
2.0 - Click Upload and Choose File, then select the theme's ZIP file. Click Install Now.
3.0 - Click Activate to use your new plugin right away.

or 

1.0 - Upload rudimentary-information.zip to the `/wp-content/plugins/` directory.
2.0 - Activate the plugin through the `Plugins` menu in WordPress Dashboard

== Frequently Asked Questions ==
= How to use this plugin =
To use this plugin simply create a new post or page, and add the following shortcode 

[theme-info slug='theme-slug' field='name'] for themes

[plugin-info slug='plugin-slug' field='name'] for plugins

= How to use Meta Box and Widget =
In this new feature, you can use Meta Box and Widget to display your information. The easiest way to do this is to create or modify a page that is under Portfolio, you will then see two new Meta Box for Theme Slug and Plugin Slug, use one or other depending on the current page that you are working on. Once this slug is set, then you can add a widget to the portfolio sidebar to display information automatically. This will only display last updated, version, and how many times the theme or plugin has been downloaded. 

== Changelog ==
= 0.0.9 = 
*Removed downloads-info.php, no need*

= 0.0.8 = 
*Found Fatal Error*

= 0.0.7 = 
*Add New Widget to support download and preview only*

= 0.0.6 = 
*Release Date - December 17, 2017*

* Add Widget Support for Themes Tags
* Bump Version to 0.0.6

= 0.0.5 =
*Release Date - December 17, 2017*

* Add Widget Support for Themes and Plugins
* Add Meta Box for Themes and Plugins to support Widget (JetPack Portfolio Only)
* Bump Version to 0.0.5

= 0.0.4 =
*Release Date - December 1, 2017*

* Fixed naming inside of classes for plugin-info.php
* Bump Vesion Number to 0.0.4

= 0.0.3 =
*Release Date - November 30, 2017*

* Bump Version to 0.0.3 for readme.txt and rudimentary-information.php
* Changed shortcode from theme-info to plugin-info 

= 0.0.2 = 
*Release Date - November 30, 2017*

* Removed Whitespace (readme.txt)
* Replace Classes for either Themes or Plugins
* Add plugin-info.php

= 0.0.1 =
*Release Date - November 30, 2017*

* Initial Release

== Upgrade Notice ==
= 0.0.1 =
*Release Date - November 30, 2017*

* Initial Release