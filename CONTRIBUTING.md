# Contributing

Please open a pull request against the default branch (`trunk`). The reviewers for the PR will be automatically assigned.

## Running unit tests

Run the following in order to install dependencies, start a local WordPress environment, and run the tests against it:

```sh
composer install
wp-env start
composer test
```

## Troubleshooting

In the event that you are facing any Docker-related problems, the following can be helpful in re-creating those docker containers:

```sh
wp-env destroy
docker volume prune
```
