# Yireo DirectoryDataCache

<!-- badges.specs.start -->
![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-directory-data-cache)
<!-- badges.specs.end -->

This module adds a separate cache (manageable from the CLI or backend) to Magento 2, when AJAX calls towards an URL `/customer/section/load` are 
made to fetch the `directory_data`. This is not a huge gain (50ms perhaps) but all performance bits matter.

Do NOT use this module if your directory data (countries and region) are customer-specific or quote-specific.

## Current status

<!-- badges.test.start -->
![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_DirectoryDataCache/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_DirectoryDataCache/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_DirectoryDataCache/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_DirectoryDataCache/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_DirectoryDataCache/compile.yml?label=compile)
<!-- badges.test.end -->
