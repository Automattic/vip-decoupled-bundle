# WPGraphQL Preview

This plugin overrides WordPress's native preview functionality and securely sends you to your decoupled frontend to preview your content. This ensures that your preview content has parity with your published content. It works by issuing a one-time use token, locked to a specific post, that can be redeemed by the frontend to obtain preview content for that post.

This plugin currently only works with our Next.js boilerplate and should be disabled if you are not using it. 

## Filters

### `vip_decoupled_token_lifetime`

Default: `3600` (one hour in seconds)

Filter the token lifetime (expiration window).

### `vip_decoupled_token_expire_on_use`

Default: `true`

Filter whether to expire the token on use. By default, tokens are "one-time use" and we mark them as expired as soon as they are used. If you want to allow tokens to be used more than once, filter this value to `false`.

Understand the security implications of this change: Within the expiration window, tokens / preview URLs become bearer tokens for viewing the associated draft post preview. Anyone who possesses them will be able to view and share the preview, even if they are not an authorized WordPress user.

