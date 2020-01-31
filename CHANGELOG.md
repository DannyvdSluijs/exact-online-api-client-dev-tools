# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.3] - 2020-01-31
### Added
- Meta file builder command was added
- Add url to Exact App Center during cli authorisation setup
- Support for Travis, Dependabot and Mergify has been added
- Add unit testing
- Added badges to Readme
### Changed
- phpunit/phpunit update from 8.5.1 to 8.5.2
- rector/rector update from 0.5.23 to 0.6.14
- symfony/http-foundation from 4.4.2 to 4.4.3
- symfony/console from 4.4.2 to 4.4.3
- symfony/filesystem 4.4.2 to 4.4.3
- squizlabs/php_codesniffer 3.5.3 to 3.5.4

## [1.1.2] - 2019-10-30
### Added
- Add static method to fetch the endpoint URI
### Changed
- Improve Authorisation setup command
- Correct PHPStan discovered errors

## [1.1.1] - 2109-09-22
### Fixed
- Corrected paths and dependencies required when used as dependency


## [1.1.0] - 2109-09-21
### Added
- New command to easily go through the OAuth 2.0 flow using the CLI to gain API access

## [1.0.0] - 2109-09-18
### Added
- New command to generate PHP classes from the ExactOnline documentation