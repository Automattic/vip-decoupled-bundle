# Changelog

## 0.2.0

- **Content blocks:** _[BREAKING]_ By default, `innerHTML` no longer removes the wrapping tag. That behavior is still available by passing a field directive `innerHTML(removeWrappingTag: true)`. The field `outerHTML` is now deprecated, since `innerHTML` provides that behavior. [#38](https://github.com/Automattic/vip-decoupled-bundle/pull/38)
- **WPGraphQL:** Updated to v1.6.7. [#34](https://github.com/Automattic/vip-decoupled-bundle/pull/34)

## 0.1.0

Initial release
