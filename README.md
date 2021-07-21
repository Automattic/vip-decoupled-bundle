# WordPress VIP decoupled plugin bundle

This plugin bundle provides a number of plugins to help you quickly setup a
decoupled WordPress application.

## WPGraphQL

WPGraphQL 1.3.8

## Content blocks

This plugin exposes Gutenberg blocks as a field on all posts registered with
WPGraphQL:

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

This will allow you to easily map Gutenberg blocks to front-end components. Posts
that do not support Gutenberg will return a single content block with the block
name `core/classic-editor`. You can use [`@wordpress/blocks`][blocks-npm] to
parse this HTML client-side, if you wish.

## Unit tests

First, start a local environment using `wp-env`:

```sh
wp-env start
wp-env run tests-wordpress bash
```

Run tests in the `tests-wordpress` container:

```sh
apt-get update
apt-get install -y mariadb-client subversion
./wp-content/plugins/vip-decoupled-bundle/bin/install-wp-tests.sh tests-wordpress root password tests-mysql latest
cd wp-content/plugins/vip-decoupled-bundle
./vendor/bin/phpunit
```

[blocks-npm]: https://www.npmjs.com/package/@wordpress/blocks?activeTab=readme#rawHandler
