# Multisite

This plugin works with multisite WordPress installations but requires additional configuration.

## Configuration

### Setting the home URL

For multisite instances, the `home` option must be set for each subsite that uses this plugin. Set it at "Network Admin > Sites > [Subsite] > Settings > Home". Alternatively, use WP-CLI to target each site:

```sh
wp --url="https://my-wp-backend.example.com/site1/" option update home "https://my-decoupled-frontend.example.com"
```

Or define a `WP_{$blog_id}_HOME` constant for each site, e.g.:

```php
define( 'WP_2_HOME', 'https://my-decoupled-frontend.example.com' );
```
