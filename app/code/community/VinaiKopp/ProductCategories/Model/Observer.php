<?php


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