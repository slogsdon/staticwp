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

### The Reasons

People love Wordpress for all sorts of reasons, but its performance is usually not one of those. There have been solutions that aim to improve performance of Wordpress sites by providing a caching layer, but usually, these solutions need configuring in order to operate effectively. Thus, a blogger, small business, etc. needs to either have the knowledge necessary to configure such a solution or contract the work out to a third party.

Security is another source of trouble for a lot of Wordpress site owners. There's a reason that there is a "Been hacked?" section in the sidebar of this subreddit. By default, Wordpress does not have everything it needs to stay ahead of malicious attackers across the Internet.

### The Idea

Static site generators are trendy these days. They provide an easy way to style a directory full of content as HTML. The resulting sites are fast and generally secure, but there is one problem with this method. Most static site generators have a single user interface, the command line. They are mostly targeted to developers.

What if there was a way to change that? What if Wordpress could take the place of a static site generator, improving a Wordpress site in the process? I figured there had to be a way.

### The Plugin

So, I wrote [StaticWP](https://github.com/slogsdon/staticwp) as a way to let site owners leverage a static site generator from within the comforts of the Wordpress admin. At its core, it generates static HTML of posts and pages on publish/update and saves the result to later present to visitors as soon as possible in the Wordpress execution stack. The comment system still works, and themes should just work (`functions.php` could be a possible pain point). Here are some goals:

- Ensure non-static home pages are generated.
- Ensure search works
- Test a variety of plugins against core functionality
- Allow for a generated site to be served completely separate from WP.
- Stay simple.

What kinds of site shouldn't use this plugin?

- Site with forums (like  Buddypress)
- Stores (like Woocommerce)
- Pretty much any site that conditionally displays content based on user state.

== Installation ==

Upload the StaticWP plugin to your site, and activate it! Yep, that's it!

== Changelog ==

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
