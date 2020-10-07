# Yireo DirectoryDataCache
This module adds a separate cache (manageable from the CLI or backend) to Magento 2, when AJAX calls towards an URL `/customer/section/load` are 
made to fetch the `directory_data`. This is not a huge gain (50ms perhaps) but all performance bits matter.

Do NOT use this module if your directory data (countries and region) are customer-specific or quote-specific.
