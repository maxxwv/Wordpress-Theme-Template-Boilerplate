# Twig/Timber Theme Developer Boilerplate

____

## Description

A basic theme boilerplate, this will get you to a (very) minimal viable product in minutes. It won't be pretty, and the template files are located in a separate library so you'll have to pick and choose and copy those over as well, but with a few small changes the basic grunt work should be taken care of.

The basic point of the theme is separation of concerns that sometimes doesn't happen in WordPress themes. The business logic is encapsulated in the functions object, while the display logic is completely contained within the template files. There's also a rudimentary automation set up using gulp that concatenates and minifies .less stylesheets and minifies and transpiles individual JavaScript files. I know some would argue that the JS files should be concatenated as well (and obviously the gulpfile can be modified to do that), but my feeling is with the growing adoption of http2 and more efficient servers, it's actually better to only load JS functionality for the pages in which it's actually used. Oftentimes there's a large amount of code for the home page, for example, that's not really needed anywhere else in the site.

Note that there are two branches to this theme. The main theme is set up to use the WordPress plugin repo version of [Timber](https://wordpress.org/plugins/timber-library/) - this version uses Twig 1.x and can run on PH 5.6+.

The twig_2x branch uses the composer installed (and preferred) version of Timber, which uses Twig 2.x and requires PHP 7.x.

Most hosting packages are getting on the ball with regard to modern PHP versions, but enough are still behind that it seemed good to offer both options. If you use the main branch and do not install and activate the Timber plugin, you'll get a warning message on both the front and back ends. If you opt for the twig_2x branch, you'll not get notifications when the Timber plugin itself is updated.

## Installation

 - Copy the files into the `wp-content/themes/[your-theme-name]` directory.
 - If you're using the plugin-in version of the theme, install and activate the Timber plugin. Otherwise, navigate to your theme directory and `composer install` to download Timber and it's dependencies.
 - Select template files and copy them into the `views` and `partials` directories.
 - Activate the theme and start developing.

## Usage

This is pretty straight forward, I hope. You'll need to update a few things before you actually get started.

 - Rename the `ThemeName` directory to your theme name.
     - If you're using the gulp automation, make the theme directory name all lower case and use dashes instead of spaces.
 - In the main `functions.php` file, change the namespace to one more appropriate to your theme.
     - Do the same in the `includes/Functions.php` file.
 - Open the `package.json` file and change the Theme Name to match your theme directory name.
     - Here you can use uppercase letters and spaces, just know that the spaces will be converted to dashes and and uppercase letters lowercased in processing.
     - For example, 'My Awesome Theme' will match the `themes/my-awesome-theme` directory.
 - Update the theme name in the `_assets/less/style.less` file to match the theme name in the `package.json` file.

While developing, the `Develop` gulp task will watch any file in the `_assets/less` and `_assets/javascript` directories for changes. All compiled files will be minimized, and the JavaScript file will have an inline sourcemap appended. The `Package` gulp task will obviously not watch for changes, and will forgo the inline sourcemap for the JavaScript file. Everything else is pretty much the same.

### Custom templates

Custom templates are easy to handle with this system. Create the view(s) necessary using Twig in the `views` or `views/partials` directory. Then create a php file in the main theme directory with the following code:

```phg
<?php
/**
 *	Template Name: Example Template
 */
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

require_once(dirname(__FILE__).'/index.php');
```

Make sure the name of the php file matches the name of the view file. The Functions file will match to the template slug to find and pass the template name to the main render function in the index.php file. It's as simple as that.

### Custom page data

The Functions file contains a method named `get_additional_page_data()` - this will look for a method within the class that matches a pattern including the custom template name. For instance, if your template slug is `my-awesome-template`, the method will look for another method called `get_my_awesome_template_additional_data()`, run that function, and return the updated context array.
