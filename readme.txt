=== StaticWP ===
Contributors: slogsdon
Tags: static, cache
Requires at least: 4.1
Tested up to: 4.1
Stable tag: trunk
License: MIT

Converts your blog into a static site.

== Description ==

Have performance issues? StaticWP converts your blog into a static site, so you don't have to worry.

== Installation ==

Upload the StaticWP plugin to your site, and activate it! Yep, that's it!

Optionally, you can set your web server to look in the storage directory (default is `staticwp/_site/`
in your uploads directory) for files prior to letting Wordpress take over control.

== Changelog ==

= 1.5.0 =
*Release Date - 25th April, 2015*

* Add actions.
* Add filters.
* Fix bug with `StaticWP\StaticWP` not qualifying `Exception` before its use.

= 1.4.2 =
*Release Date - 25th April, 2015*

* Fix issue with preload.
* Fix issue with uninstall.

= 1.4.1 =
*Release Date - 9th March, 2015*

* Fix activation problem.

= 1.4.0 =
*Release Date - 4th March, 2015*

* Make preloading safer.
* Ensure more than posts are compiled.
* Allow comments to be added
* Fix bug when files are recompiled.

= 1.3.0 =
*Release Date - 4th March, 2015*

* Refactor frontend and admin into separate classes.
* Abstract HTML into templates and `StaticWPView`.
* Add admin menu pages.
* Allow users to preload site.

= 1.2.0 =
*Release Date - 4th March, 2015*

* Improve directory resolution.

= 1.1.1 =
*Release Date - 4th March, 2015*

* Fix bug with plugin name.

= 1.1.0 =
*Release Date - 3rd March, 2015*

* Add deactivation hook for cleanup.
* Add uninstall hook for cleanup.
* Move storage directory to uploads directory.

= 1.0.0 =
*Release Date - 3rd March, 2015*

* Initial Release
* Does basic static file generation.
* Sends file if it exists only for `GET` requests.
