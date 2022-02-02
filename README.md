# WordPress VIP decoupled plugin bundle

This plugin bundle provides a number of plugins to help you quickly setup a decoupled WordPress application. It is designed to support VIP’s [Next.js boilerplate][nextjs-boilerplate] but can be used to support any decoupled frontend. It solves a number of common problems facing decoupled sites, including:

- Previewing
- Permalinks
- Feeds
- Rendering block-based content

> ⚠️ This project is under active development. If you are a VIP customer, please let us know if you'd like to use this plugin and we can provide additional guidance. Issues and PRs are welcome. 💖

## Setting your `WP_HOME`

WordPress needs to know the address of your frontend so that it can point permalinks, feed links, and other URLs to the correct destination. WordPress uses the `WP_HOME` setting for this, but by default it is set to the same address that WordPress is served from. You must update it to the address of your decoupled frontend.

You can make this change in the Dashboard at Settings > General > Site Address (URL). Alternatively, you can define this constant in your `wp-config.php` or [`vip-config.php` on VIP][vip-config]:

```php
define( 'WP_HOME', 'https://my-decoupled-frontend.example.com' );
```

See [WordPress documentation for other options](https://wordpress.org/support/article/changing-the-site-url/#changing-the-site-url).

That's all the configuration that's needed to support your decoupled frontend. If you are using VIP's Next.js boilerplate, [head over to the README][nextjs-boilerplate] to get your frontend up and running.

## Settings and sub-plugins

This plugin provides a settings page in the WordPress Dashboard at Settings > VIP Decoupled. There, you'll find your GraphQL endpoint. You can also see (and optionally disable) the "sub-plugins", described below, that this plugin provides.

### WPGraphQL

[WPGraphQL][wp-graphql] is a [GraphQL][graphql] API for WordPress, and provides the backbone of how your decoupled frontend will load content from WordPress. GraphQL is a relatively new but very powerful query language that provide a good developer experience.

When updates are pushed out to WPGraphQL, we will update this plugin after evaluating it for compatibility and performance. If you need to run a different version of WPGraphQL, you can disable the bundled version and activate your own.

### WPGraphQL Content blocks

This plugin exposes Gutenberg blocks as a field named `contentBlocks` on all post types that support a content editor:

```gql
query AllPosts {
  posts {
    nodes {
      id
      title
      contentBlocks {
        blocks {
          attributes {
            name
            value
          }
          name
          innerHTML
        }
        isGutenberg
        version
      }
    }
  }
}
```

This will allow you to easily map Gutenberg blocks to front-end components. Posts that do not support Gutenberg will return a single content block with the block name `core/classic-editor`, which contains the full `innerHTML` of the post.

See our [Next.js boilerplate][nextjs-boilerplate] for an example of how to use and render these blocks.

### WPGraphQL Preview

This plugin overrides WordPress's native preview functionality and securely sends you to your decoupled frontend to preview your content. This ensures that your preview content has parity with your published content. It works by issuing a one-time use token, locked to a specific post, that can be redeemed by the frontend to obtain preview content for that post.

This plugin currently only works with our Next.js boilerplate and should be disabled if you are not using it. 

## Running unit tests

### Pre-requisites

In order to run the unit tests, you will need the following:

- [Docker](https://www.docker.com/get-started)
- [Composer](https://getcomposer.org/download/)
- [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)
- PHP <= 7.4

### Commands to run the tests

Run the following in order to install the right php packages, start a local wordpress environment and run the tests against it:

```sh
composer install
wp-env start
composer test-local
```

### Troubleshooting

In the event that you are facing any docker container problems, the following should likely be helpful in re-creating those docker containers:

```sh
wp-env destroy
docker volume prune
```

It's also helpful to delete all the images pertaining to what was destroyed above.

[graphql]: https://graphql.org
[nextjs-boilerplate]: https://github.com/Automattic/vip-go-nextjs-skeleton
[vip-config]: https://docs.wpvip.com/technical-references/vip-codebase/vip-config-directory/
[wp-graphql]: https://wpgraphql.com
