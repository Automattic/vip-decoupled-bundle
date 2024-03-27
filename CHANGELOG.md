# Changelog

## 1.2.1

- [**Add preview URL filter**](https://github.com/Automattic/vip-decoupled-bundle/pull/81): Adds the ability to modify preview URL destinations.
- [**Update VIP Block Data API v1.2.1**](https://github.com/Automattic/vip-decoupled-bundle/pull/82): Update the VIP Block Data API to `1.2.1`, which [supports `rich-text` attributes](https://github.com/Automattic/vip-block-data-api/releases/tag/1.2.1) in WordPress 6.5.

## 1.2.0

- [**VIP Block Data API**](https://github.com/Automattic/vip-block-data-api) plugin added to bundle as an alternative to Content Blocks.

## 1.1.0

- **WPGraphQL:** Updated to v1.19.0.

## 1.0.0

This marks the release of v1.0 of the plugin.

- **WPGraphQL:** Updated to v1.17.0.

## 0.3.0

- Adjusted the minimum support version for WP, PHP as well as the WP version that it's tested upto.
- Added a link to this repo, in the plugin's uri so it's easy to navigate to.
- Added installation instructions in the README.

## 0.2.0

- **Content blocks:** _[BREAKING]_ By default, `innerHTML` no longer removes the wrapping tag. That behavior is still available by passing a field directive `innerHTML(removeWrappingTag: true)`. The field `outerHTML` is now deprecated, since `innerHTML` provides that behavior. [#38](https://github.com/Automattic/vip-decoupled-bundle/pull/38)
- **WPGraphQL:** Updated to v1.6.7. [#34](https://github.com/Automattic/vip-decoupled-bundle/pull/34)

## 0.1.0

Initial release
