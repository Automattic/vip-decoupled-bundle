# WordPress VIP Decoupled Plugin Bundle

This plugin bundle provides a number of plugins to help you quickly setup a decoupled WordPress application. It is designed to support VIP’s [Next.js boilerplate][nextjs-boilerplate] but can be used to support any decoupled frontend. It solves a number of common problems facing decoupled sites, including:

- Previewing
- Permalinks
- Feeds
- Exposing structured data for block-based content

> ⚠️ This project is under active development. If you are a VIP customer, please let us know if you'd like to use this plugin and we can provide additional guidance. Issues and PRs are welcome. 💖

## Table of contents
- [Installation](#installation)
	- [Plugin activation](#plugin-activation)
- [Getting started](#getting-started)
- [Configuration](#configuration)
	- [Setting the home URL](#setting-the-home-url)
- [Jetpack Configuration](#jetpack-configuration)
	- [Plugin settings](#plugin-settings)
- [Sub-plugins](#sub-plugins)
	- [WPGraphQL](#wpgraphql)
	- [WPGraphQL Preview](#wpgraphql-preview)
	- [Block Data Plugins](#block-data-plugins)
		- [WPGraphQL Content blocks](#wpgraphql-content-blocks)
		- [VIP Block Data API](#vip-block-data-api)
		- [Which Plugin Should I Choose?](#which-plugin-should-i-choose)
- [Contributing](#contributing)

## Installation

The latest version of the plugin can be downloaded from the [repository's Releases page][repo-releases]. Unzip the downloaded plugin and add it to the `plugins/` directory of your site's GitHub repository.

### Plugin activation

For VIP sites, we recommend [activating plugins with code][wpvip-plugin-activate].

For Non-VIP sites, activate the plugin in the WordPress Admin dashboard using the following steps:

1. Navigate to the WordPress Admin dashboard as a logged-in user.
2. Select **Plugins** from the lefthand navigation menu.
3. Locate the "VIP Decoupled Plugin Bundle" plugin in the list and select the "Activate" link located below it.

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

## Jetpack Configuration

Prior to version 11.2, Jetpack had syncing functionality that fires on WordPress shutdown hooks. This can cause performance issues for API requests. A more performant architecture is used in all versions of Jetpack since 11.2. If you are unable to update Jetpack, you can [install a VIP written plugin][vip-jetpack-sync-cron] to offload the sync to cron. It also detects the verson of Jetpack and will not conflict if you upgrade Jetpack in the future.

### Plugin settings

This plugin provides a settings page in the Admin Dashboard, found at Settings > VIP Decoupled. There, you'll find your GraphQL endpoint. You can also see (and optionally disable) the "sub-plugins", described below, that this plugin provides.

That's all the configuration that's needed to support your decoupled frontend. If you are using VIP's Next.js boilerplate, [head over to the README][nextjs-boilerplate] to get your frontend up and running.

## Sub-plugins

This plugin bundle provides a number of "sub-plugins" that are commonly useful in decoupled environments. You are free to disable any of them and bring your own alternatives.

### WPGraphQL

[WPGraphQL][wp-graphql] is a [GraphQL][graphql] API for WordPress, and provides the backbone of how your decoupled frontend will load content from WordPress. GraphQL is a relatively new but very powerful query language that provide a good developer experience.

When updates are pushed out to WPGraphQL, we will update this plugin after evaluating it for compatibility and performance. If you need to run a different version of WPGraphQL, you can disable the bundled version and activate your own.

### WPGraphQL Preview

This plugin overrides WordPress's native preview functionality and securely sends you to your decoupled frontend to preview your content. This ensures that your preview content has parity with your published content. It works by issuing a one-time use token, locked to a specific post, that can be redeemed by the frontend to obtain preview content for that post.

**This plugin currently only works with our Next.js boilerplate** and should be disabled if you are not using it. If you are interested in using this plugin for other frontend frameworks, please see the [preview `README`][preview-readme].

### Block Data Plugins

There are 2 sub-plugins bundled for exposing the Gutenberg blocks - WPGraphQL Content Blocks and VIP Block Data API. In the near future, WPGraphQL Content Blocks will be deprecated in favour of just including the VIP Block Data API.

##### WPGraphQL Content blocks

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

##### VIP Block Data API

This plugin exposes Gutenberg blocks as JSON data, with integrations for both the official WordPress REST API and WPGraphQL. The GraphQL API exposes the blocks under a field name `blocksData` on all post types that support a content editor:

```graphQL
query GetPost {
  post(id: "1", idType: DATABASE_ID) {
    blocksData {
      blocks {
        id
        name
        attributes {
          name
          value
        }
        innerBlocks {
          name
          parentId
          id
          attributes {
            name
            value
          }
        }
      }
    }
  }
}
```

Posts that do not support Gutenberg are not supported by this plugin. For more information, refer to the documentation [here](https://github.com/Automattic/vip-block-data-api).

#### Which Plugin Should I Choose?

We recommend the VIP Block Data API plugin, as the plugin of choice for exposing the Gutenberg blocks.

However, if you require exposing the Gutenberg blocks as HTML structured data rather than JSON data, then using the WPGraphQL Content Blocks plugin would be recommended. This would allow for the decoupled app, to easily render the blocks via the `dangerouslySetInnerHTML` method rather than having to individually design each block's corresponding component.

That being said, we intend to get this feature included in the VIP Block Data API plugin in the near future. This would allow for the eventual deprecation of the WPGraphQL Content Block plugin.

## Contributing

Refer [here](CONTRIBUTING.md) for how to contribute to this plugin's development.

<!-- Links -->
[graphql]: https://graphql.org
[mulisite-file]: MULTISITE.md
[preview-readme]: preview/README.md
[nextjs-boilerplate]: https://github.com/Automattic/vip-go-nextjs-skeleton
[wp-graphql]: https://wpgraphql.com
[wp-env]: https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/
[wp-env-file]: wp-env.json
[vip-jetpack-sync-cron]: https://github.com/Automattic/vip-jetpack-sync-cron
[repo-releases]: https://github.com/Automattic/vip-decoupled-bundle/releases
[wpvip-plugin-activate]: https://docs.wpvip.com/how-tos/activate-plugins-through-code/
