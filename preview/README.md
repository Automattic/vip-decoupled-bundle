# WPGraphQL Preview

This plugin overrides WordPress's native preview functionality and securely sends you to your decoupled frontend to preview your content. This ensures that your preview content has parity with your published content. It works by issuing a one-time use token, locked to a specific post, that can be redeemed by the frontend to obtain preview content for that post.

**This plugin currently only works with our [Next.js boilerplate][nextjs-boilerplate]** and should be disabled if you are not using it. If you are interested in using this plugin for other frontend frameworks, there are two main components that need to be implemented:

1. Redirect logic that intercepts preview requests that are missing a token and sends them back to WordPress so that a token can be generated. See [this config block][config-block] in our Next.js boilerplate. This is necessary because of [the lack of filterability of preview links in the block editor][preview-issue].
2. A preview route that will extract the token and add it to a special request header for preview queries. See [this preview route][preview-route] in our Next.js boilerplate.

## Filters

### `vip_decoupled_token_lifetime`

Default: `3600` (one hour in seconds)

Filter the token lifetime (expiration window).

### `vip_decoupled_token_expire_on_use`

Default: `true`

Filter whether to expire the token on use. By default, tokens are "one-time use" and we mark them as expired as soon as they are used. If you want to allow tokens to be used more than once, filter this value to `false`.

Understand the security implications of this change: Within the expiration window, tokens / preview URLs become bearer tokens for viewing the associated draft post preview. Anyone who possesses them will be able to view and share the preview, even if they are not an authorized WordPress user.

[config-block]: https://github.com/Automattic/vip-go-nextjs-skeleton/blob/d0dd9d91597a83007fd5eec2d008f96e1086dab3/next.config.js#L91-L113
[nextjs-boilerplate]: https://github.com/Automattic/vip-go-nextjs-skeleton
[preview-issue]: https://github.com/WordPress/gutenberg/issues/13998
[preview-route]: https://github.com/Automattic/vip-go-nextjs-skeleton/blob/d0dd9d91597a83007fd5eec2d008f96e1086dab3/pages/preview/%5Btoken%5D/%5Bid%5D.tsx
