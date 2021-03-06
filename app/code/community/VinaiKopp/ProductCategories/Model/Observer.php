<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category   Vinai Kopp
 * @package    VinaiKopp_ProductCategories
 * @copyright  Copyright (c) 2014 Vinai Kopp http://netzarbeiter.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class VinaiKopp_ProductCategories_Model_Observer
{
    /**
     * Clear the category list block for this product
     * 
     * @param Varien_Event_Observer $args
     */
    public function catalogProductSaveCommitAfter(Varien_Event_Observer $args)
    {
        $product = $args->getProduct();
        if ($product->getId()) {
            $tag = Mage_Catalog_Model_Product::CACHE_TAG . '_' . $product->getId();
            Mage::app()->cleanCache(array($tag));
        }
    }
}