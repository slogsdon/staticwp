# StaticWP

Converts your blog into a static site.

## Description

Have performance issues? StaticWP converts your blog into a static site, so you don't have to worry.

## Installation

Upload the StaticWP plugin to your site, and activate it! Yep, that's it!

Optionally, you can set your web server to look in the storage directory (default is `staticwp/_site/`
in your uploads directory) for files prior to letting Wordpress take over control.

## Hooks

Need to modify how StaticWP works? Look to an action or a filter to accomplish what you need.

### Actions

#### `staticwp_pre_cache_hit`

Called prior to outputting a cache hit to the client.

Params:

- `$_SERVER['REQUEST_URI']` - `string|null`

#### `staticwp_post_cache_hit`

Called after outputting a cache hit to the client.

Params:

- `$_SERVER['REQUEST_URI']` - `string|null`

#### `staticwp_cache_miss`

Called when a requested post does not exist in the cache.

Params:

- `$_SERVER['REQUEST_URI']` - `string|null`

#### `staticwp_pre_cache_update`

Called prior to saving a post's static HTML to disk.

Params:

- `$post_id` - `integer`

#### `staticwp_post_cache_update`

Called after saving a post's static HTML to disk.

Params:

- `$post_id` - `integer`

### Filters

#### `staticwp_preload_post_types`

Allows a developer to modify the list of post types to preload.
Default/starting value is `array('post', 'page')`.

Params:

- `$post_types` - `array(string)`

#### `staticwp_preload_{$post_type}_posts`

Allows a developer to modify the list of posts to preload. By
default, all published posts of type `$post_type` will be used
and will be ordered by `post_date` in descending order.

Params:

- `$post_ids` - `array(integer)`

#### `staticwp_cache_hit_contents`

Allows a developer to modify the contents of a post's static
HTML prior to ouputting to the client.

Params:

- `$contents` - `string`

#### `staticwp_cache_update_contents`

Allows a developer to modify the contents of a post's static
HTML prior to saving to disk.

Params:

- `$contents` - `string`
- `$post_id` - `integer`

#### `staticwp_cache_destination`

Allows a developer to modify the storage directory for the static
HTML files.

Params:

- `$dir` - `string`

## Changelog

### 1.5.0

*Release Date - 25th April, 2015*

- Add actions.
- Add filters.
- Fix bug with `StaticWP\StaticWP` not qualifying `Exception` before its use.

### 1.4.2

*Release Date - 25th April, 2015*

- Fix issue with preload.
- Fix issue with uninstall.

### 1.4.1

*Release Date - 9th March, 2015*

- Fix misuse of `wp_mkdir`.

### 1.4.0

*Release Date - 9th March, 2015*

- Make preloading safer.
- Ensure more than posts are compiled.
- Allow comments to be added
- Fix bug when files are recompiled.

### 1.3.0

*Release Date - 4th March, 2015*

- Refactor frontend and admin into separate classes.
- Abstracted HTML into templates and `StaticWPView`.
- Add admin menu pages.
- Allow users to preload site.

### 1.2.0

*Release Date - 4th March, 2015*

- Improve directory resolution.

### 1.1.1

*Release Date - 4th March, 2015*

- Fix bug with plugin name.

### 1.1.0

*Release Date - 3rd March, 2015*

- Add deactivation hook for cleanup.
- Add uninstall hook for cleanup.
- Move storage directory to uploads directory.

### 1.0.0

*Release Date - 3rd March, 2015*

- Initial release.
- Does basic static file generation.
- Sends file if it exists only for `GET` requests.

## License

StaticWP is released under the MIT License.

See [LICENSE](https://github.com/slogsdon/staticwp/blob/master/LICENSE) for details.
