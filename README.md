# webpack-asset-map

webpack plugin that generates a map of assets with hashed filenames, WordPress utility for enqueuing those files, and WordPress theme demonstrating how to use them.

## tl;dr

If your WordPress site has caching, and you bundle and minify your JS and CSS files with [webpack](https://webpack.js.org/), this plugin helps you give users the latest version of the files without bothering about version numbers.

## Table of Contents

1. [Background](#1-background)
    1. [Caching and cache-busting](#i-caching-and-cache-busting)
    1. [webpack](#ii-webpack)
    1. [Enqueuing webpack-built files in WordPress](#iii-enqueuing-webpack-built-files-in-wordpress)
1. [What's in the project](#2-whats-in-the-project)
    1. [webpack plugin](#i-webpack-plugin)
    1. [WordPress helper class](#ii-wordpress-helper-class)
    1. [Demo WordPress Theme](#iii-demo-wordpress-theme)
1. [Try the demo](#3-try-the-demo)
    1. [Requirements](#i-requirements)
    1. [Setup setps](#ii-setup-steps)
1. [Use the code in your own site](#4-use-the-code-in-your-own-site)
    1. [webpack plugin](#i-webpack-plugin)
    1. [WordPress utility](#ii-wordpress-utility)

## 1. Background

:info: If you don't need an intro to caching, webpack and enqueuing files in WordPress, skip ahead to [What's in the project](#2-whats-in-the-project).

### i. Caching and cache-busting

A lot of WordPress sites [cache](https://wordpress.org/support/article/optimization-caching/) their front end files. This is a very good practice that can help your site perform better: if you're not doing it already you should definitely consider it.

A side effect of caching is that after you upload a change to a file, your site visitors may continue to get the old version of the file because it's still hanging around in the cache. You can force it to update by deleting it from the cache. Or you can also ensure users get the latest version by changing the URL you use to include the file in your site. This is called "cache busting".

WordPress has a good feature to help you do this: the version parameters in `wp_enqueue_style` and `wp_enqueue_script`. If you enqueue a JS file like this:

```php
wp_enqueue_script( 'my-script', get_get_stylesheet_directory_uri() . '/js/my-script.js', null, 'v1.1' );
```

WordPress will add your version number to the URL when it includes the file on the front end.

```
http://example.com/wp-content/themes/demo/js/my-script.js?ver=1.1
``` 

As far as the cache is concerned, this makes it a new file, so your users will get your updated JS.

This is fine for many sites, but if you make a lot of updates to your front end files, updating the version number every time can be a chore, and something you can easily forget to do. webpack offers a nice way of simplifying this task.

### ii. webpack

A lot of developers use webpack to process their CSS and JS files. This project includes a simple webpack setup that will transpile ES6 files to JavaScript and SCSS files to CSS, bundle and minify them. I swiped it from Anton Melnyk's excellent tutorial [How to configure Webpack 4 from scratch for a basic website](https://dev.to/pixelgoo/how-to-configure-webpack-from-scratch-for-a-basic-website-46a5) – see Anton's post for details about what each part of the build does.

webpack has a nice feature that provides an alternative to version number URL parameters. You can configure it include a content hash in the name of the output files, like the `381b4726557c6178c94e` in `bundle.381b4726557c6178c94e.min.js`. This string is a representation of the content of the file. If anything changes in the file, the content hash (and the filename) will change. If nothing's changed, it'll stay the same.

### iii. Enqueuing webpack-built files in WordPress

But how do you enqueue the files in WordPress if the filename changes every time you change something in the file? The utility in `class-built-assets.php` is designed to help with that. When someone views a page on your site, it checks if the `map.json` file has changed, by comparing the modified timestamp with a timestamp it's saved in a [transient](https://developer.wordpress.org/apis/handbook/transients/). If it has, it loads the list of files from `map.json` and saves it in another transient for future use. When you pass the plain name of a file to the utility's `get_hashed_filename` method, it looks up the file in the list and returns the name with the content hash.

This means you don't have to worry about updating the version number in your `wp_enqueue_style` and `wp_enqueue_script` calls, or flushing the cache when you change your front end files: visitors to your site will automatically get the latest version of the files. 

## 2. What's in the project

### i. webpack plugin

This repo includes a webpack plugin, `webpack-plugin-asset-map.js`. If your webpack build is configured to output files with a content hash in the name, the plugin generates a JSON file with a list of those files.

### ii. WordPress helper class 

There's also a helper class in `class-built-assets.php` which enables you to enqueue built assets in your WordPress theme without using version numbers to bust the cache.

### iii. Demo WordPress theme  
 
The theme shows how you can process your theme's front end files with webpack, and use the webpack plugin and WordPress helper to enqueue them without needing to update version numbers.

## 3. Try the demo

### i. Requirements

- A local WordPress site.
- Node and `npm`.

### ii. Setup steps 

1. Open a terminal and check this project out from the [repo](https://github.com/andfinally/webpack-asset-map) into your site's `wp-content/themes` folder:

    ```bash
    git checkout https://github.com/andfinally/webpack-asset-map.git
    ```

2. Change into the theme folder:

    ```bash
    cd wp-content/themes/demo
    ```

3. Install webpack and its dependencies:

    ```bash
   npm i 
   ``` 

4. Run the webpack build:

    ```bash
   npm run build 
   ```
   
   This will process the JS and SCSS files in the `assets` folder and output three files into a new `dist` folder. Note the content hashes on the filenames, for example the `d8e8036abe33143cd7b1` in `bundle.d8e8036abe33143cd7b1.min.js`. 
   
   With this command, webpack will also start watching the theme folder for JS and CSS changes, and rebuild the files when you save a change.
   
   <img src="https://github.com/andfinally/webpack-asset-map/raw/main/assets/img/files.png" width="320" />
   
5. Set the `demo` theme as the active theme in Wordpress `wp-admin/themes.php`.

    <img src="https://github.com/andfinally/webpack-asset-map/raw/main/assets/img/themes.png" width="500" />
    
6. The site should have the default TwentyTwenty theme look on the front end.

    <img src="https://github.com/andfinally/webpack-asset-map/raw/main/assets/img/before.png" width="500" />
   
7. Uncomment the commented code in `assets/css/demo.scss` and `assets/js/demo.js` and save the files. The build should run: the content hashes on the processed files should be different, and the site should show the changes you've just saved:

    <img src="https://github.com/andfinally/webpack-asset-map/raw/main/assets/img/after.png" width="500" />

## 4. Use the code in your own site

### i. webpack plugin

To use the webpack plugin, save `webpack-plugin-asset-map.js` to your theme, and include it in your `webpack.config.js` file:

```js
const AssetMapPlugin = require( './webpack-plugin-asset-map' );
```

Then add it to the `plugins` part of your webpack config:

```js
plugins: [
    new AssetMapPlugin(),
],
```

The plugin expects your built files to be named like `[name].[contenthash].min.[extension]`, for example `bundle.381b4726557c6178c94e.min.js`. You can configure this for JS files in the `output` entry of your webpack config.

```js
output: {
    path: path.resolve( __dirname, 'dist' ),
    filename: 'bundle.[contenthash].min.js'
},
```

For CSS files, you can configured the filename in the output of the `MiniCssExtractPlugin` plugin.

```js
new MiniCssExtractPlugin( {
    filename: 'bundle.[contenthash].min.css'
} ),
``` 

### ii. WordPress utility

Save `class-built-assets.php` to your theme and include it. For example in your `functions.php` add:

```php
require_once 'class-built-assets.php';
```

Whenever you want to enqueue an asset from the webpack build, call `Built_Assets::get_hashed_filename` to get the file's current name:

```php
wp_enqueue_style(
    'demo-style',
    get_stylesheet_directory_uri() . '/dist/' . Built_Assets::get_hashed_filename( 'bundle.min.css' )
);
```
