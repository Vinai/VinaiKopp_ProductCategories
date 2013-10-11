List Product Categories
=======================
Display a list of the categories associated with the a product on the product detail page on the frontend.  
For each category the parent categories are displayed, too.

Facts
-----
- version: check the [config.xml](https://github.com/Vinai/VinaiKopp_ProductCategories/blob/master/app/code/community/VinaiKopp/ProductCategories/etc/config.xml)
- extension key: - This extension is not on Magento Connect (github only) -
- [extension on GitHub](https://github.com/Vinai/VinaiKopp_ProductCategories)
- [direct download link](https://github.com/Vinai/VinaiKopp_ProductCategories/zipball/master)

Description
-----------
Display a list of the categories associated with the a product on the product detail page on the frontend.  
For each category the parent categories are displayed, too.  

![Screenshot of default block template](https://raw.github.com/Vinai/VinaiKopp_ProductCategories/media/ProductCategoryList-screenshot-frontend.png)

Once the blocks are generated, they are cached for better system performance.

The automatic addition of the categories list to product pages can be turned off in the system configuration at  

    Vinai Kopp Extensions > Product Categories List > General

![Configuration Screenshot](https://raw.github.com/Vinai/VinaiKopp_ProductCategories/media/ProductCategoryList-screenshot-backend.png)

If you don't want to display the block automatically at the bottom of all product pages, disable the feature in the system configuration, and add the block manually to your product template at the place you want it to display using the code

    <?php echo $this->getBlockHtml('product.categories.list') ?>

**Note:** This only works on product pages.

The reason for this module? I was asked for it nicely. I was told that having this supposedly is good for SEO purposes.

Compatibility
-------------
- Magento >= 1.4 (since ```Mage_Core_Block_Abstract::getCacheKeyInfo()``` exists)

Installation Instructions
-------------------------
1. Unpack the extension ZIP file in your Magento root directory.
2. Clear the cache.
3. Log out of the admin and log back in again to refresh the access control lists for the system configuration.
4. If you use the Magento compiler, recompile.

Support
-------
If you have any issues with this extension, open an issue on GitHub (see URL above)

Contribution
------------
Any contributions are highly appreciated. The best way to contribute code is to open a
[pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Vinai Kopp  
[http://www.netzarbeiter.com](http://www.netzarbeiter.com)  
[@VinaiKopp](https://twitter.com/VinaiKopp)  

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) 2013 Vinai Kopp