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

[blocks-npm]: https://www.npmjs.com/package/@wordpress/blocks?activeTab=readme#rawHandler
