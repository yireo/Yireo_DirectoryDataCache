# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.0.1] - 27 November 2026
### Added
- Separate "Directory Data" cache type that caches the `directory_data` section of `/customer/section/load`

### Changed
- Directory data cache is scoped per store and locale, and cleaned together with config and translation caches
- Corrupt cache entries are removed and fall back to the original data
- Require `magento/framework` ^103.0 instead of any version; license set to OSL-3.0
- Supported PHP versions are now 8.2 to 8.5 (PHP 8.1 dropped)

### Fixed
- Missing `Magento_Checkout` and `Magento_Store` module dependencies
