# WordPress VIP decoupled plugin bundle

This plugin bundle provides a number of plugins to help you quickly setup a decoupled WordPress application. It is designed to support VIP’s [Next.js boilerplate][nextjs-boilerplate] but can be used to support any decoupled frontend. It solves a number of common problems facing decoupled sites, including:

- Previewing
- Permalinks
- Feeds
- Exposing structured data for block-based content

> ⚠️ This project is under active development. If you are a VIP customer, please let us know if you'd like to use this plugin and we can provide additional guidance. Issues and PRs are welcome. 💖


## Getting started

This plugin is ready for local development using [`wp-env`][wp-env] and Docker:

```sh
wp-env start
```

This command will start a local WordPress environment, activate the plugin, and be ready for GraphQL requests from our [Next.js boilerplate][nextjs-boilerplate] (which must be set up separately).

The default credentials for the Admin Dashboard (provided by `wp-env`) are U: `admin` / P: `password`.


## Configuration

### Setting the home URL

WordPress needs to know the address of your frontend so that it can point previews, permalinks, and other URLs to the correct destination. WordPress uses the `home` option for this, but by default it is set to the same address that WordPress is served from. This plugin requires you to update `home` to the address of your decoupled frontend. Additionally, it handles a few edge cases, like feed URLs, that we want to serve from WordPress (see `./urls/urls.php`).

If you are using [`wp-env`][wp-env] for local development, this step is already done for you in [`.wp-env.json`][wp-env-file]. By default, it points to `http://localhost:3000`; if you make changes, you'll need to `stop` and `start` your environment for the changes to take effect.

For other environments, you'll need to make this change manually. The easiest method is using WP-CLI:

```sh
wp option update home "https://my-decoupled-frontend.example.com"
```

You can also define a constant in code that overrides the value of the option:

```php
define( 'WP_HOME', 'https://my-decoupled-frontend.example.com' );
```

It is also possible to make this change in the Admin Dashboard, but **be careful**: In the Admin Dashboard, WordPress labels the `home` option inconsistently and in ways that can be confusing and misleading. A related but separate option named `siteurl` governs where WordPress serves the Dashboard and other core functionality. You should not edit `siteurl`; however, WordPress sometimes labels the `home` option as "Site Address (URL)."

Multisite installations require different configuration. Please see [`MULTISITE.md`][multisite-file].

### Plugin settings

This plugin provides a settings page in the Admin Dashboard, found at Settings > VIP Decoupled. There, you'll find your GraphQL endpoint. You can also see (and optionally disable) the "sub-plugins", described below, that this plugin provides.

That's all the configuration that's needed to support your decoupled frontend. If you are using VIP's Next.js boilerplate, [head over to the README][nextjs-boilerplate] to get your frontend up and running.


## Sub-plugins

This plugin bundle provides a number of "sub-plugins" that are commonly useful in decoupled environments. You are free to disable any of them and bring your own alternatives.

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

**This plugin currently only works with our Next.js boilerplate** and should be disabled if you are not using it. If you are interested in using this plugin for other frontend frameworks, please see the [preview `README`][preview-readme].

[graphql]: https://graphql.org
[mulisite-file]: MULTISITE.md
[preview-readme]: preview/README.md
[nextjs-boilerplate]: https://github.com/Automattic/vip-go-nextjs-skeleton
[wp-graphql]: https://wpgraphql.com
[wp-env]: https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/
[wp-env-file]: wp-env.json
