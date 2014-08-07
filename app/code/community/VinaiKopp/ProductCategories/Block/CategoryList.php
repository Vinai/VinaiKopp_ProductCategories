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

class VinaiKopp_ProductCategories_Block_CategoryList extends Mage_Catalog_Block_Product_Abstract
{
    const CACHE_TAG = 'product_category_list';

    /**
     * The first of the product categories (for css class rendering)
     * 
     * @var Mage_Catalog_Model_Category
     */
    protected $_firstProductCat;


    /**
     * The last of the product categories (for css class rendering)
     * 
     * @var Mage_Catalog_Model_Category
     */
    protected $_lastProductCat;
    
    /**
     * List of parent categories for each direct product category
     * 
     * @var array
     */
    protected $_parents = array();
    
    /**
     * Set a default template
     */
    protected function _construct()
    {
        $this->setTemplate('vinaikopp/productcategories/list.phtml');
        parent::_construct();
    }

    /**
     * No cache expiry (cache forever)
     * 
     * @return bool
     */
    public function getCacheLifetime()
    {
        return false;
    }

    /**
     * Clear cache when any category or the associated product is edited
     * 
     * @return array
     */
    public function getCacheTags()
    {
        $tags = parent::getCacheTags();
        $tags[] = self::CACHE_TAG;
        $tags[] = Mage_Catalog_Model_Category::CACHE_TAG;
        $tags[] = Mage_Catalog_Model_Product::CACHE_TAG . '_' . $this->getProduct()->getId();
        return $tags;
    }

    /**
     * Cache this block for each product separately
     * 
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $info = parent::getCacheKeyInfo();
        $info[] = 'PRODUCT_' . $this->getProduct()->getId();
        return $info;
    }

    /**
     * Return all categories associated with product
     * 
     * @return Mage_Catalog_Model_Category[]
     */
    public function getCategories()
    {
        $categories = $this->_getData('categories');
        if (is_null($categories)) {
            $categories = $this->getProduct()->getCategoryCollection()
                ->addIsActiveFilter()
                ->addNameToResult()
                ->addUrlRewriteToResult()->getItems();

            // Set to avoid recursion because getAllParentCategories() is called
            // sorting, which in turn calls getCategories() again.
            $this->setCategories($categories);
            
            usort($categories, array($this, '_sortByParentBaseCategory'));
            
            // Set reference for css classes
            $this->_lastProductCat  = end($categories);
            $this->_firstProductCat = reset($categories);
            
            // Set the sorted categories for use in template
            $this->setCategories($categories);
        }
        return $categories;
    }

    /**
     * Return categories associated with current product + parent categories
     * 
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getAllParentCategories()
    {
        $allParentCategories = $this->_getData('all_parent_categories');
        if (is_null($allParentCategories)) {
            $ids = $categories = array();
            foreach ($this->getCategories() as $category) {
                /** @var Mage_Catalog_Model_Category $category */
                $ids = array_merge($ids, $category->getParentIds());
            }
            $alreadyLoadedIds = array_keys($this->getCategories());
            $ids = array_diff(array_unique($ids, SORT_NUMERIC), $alreadyLoadedIds);
            
            /** @var Mage_Catalog_Model_Resource_Category_Collection $allParentCategories */
            $allParentCategories = Mage::getModel('catalog/category')
                ->getCollection()
                ->addIdFilter($ids)
                ->addIsActiveFilter()
                ->addNameToResult()
                ->addUrlRewriteToResult()
                ->addFieldToFilter('level', array('gt' => 1));

            foreach ($this->getCategories() as $category) {
                $allParentCategories->addItem($category);
            }
            $this->setAllParentCategories($allParentCategories);
        }
        return $allParentCategories;
    }

    /**
     * Return all parent categories for the given category
     * 
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Model_Category[]
     */
    public function getParentCategoriesForCategory(Mage_Catalog_Model_Category $category)
    {
        if (! isset($this->_parents[$category->getId()])) {
            $parents = array();
            foreach ($category->getPathIds() as $parentId) {
                if ($parent = $this->getAllParentCategories()->getItemById($parentId)) {
                    // Set flag for css class
                    if (! $parents) {
                        $parent->setIsBase(true);
                    }
                    $parents[] = $parent;
                }
            }
            // Set flag for css class and separator
            if ($parent) {
                $parent->setIsHead(true);
            }
            $this->_parents[$category->getId()] = $parents;
        }
        return $this->_parents[$category->getId()];
    }


    /**
     * Called via usort() to sort an array of categories by name
     *
     * @param $a
     * @param $b
     * @return int
     * @see VinaiKopp_ProductCategories_Block_CategoryList::getCategories()
     */
    protected function _sortByParentBaseCategory($a, $b)
    {
        $aParents = $this->getParentCategoriesForCategory($a);
        $bParents = $this->getParentCategoriesForCategory($b);
        
        for ($lvl = 0, $maxLvl = count($aParents); $lvl < $maxLvl; $lvl++) {
            if (! isset($bParents[$lvl])) {
                return 1; // a is larger b if there is no b
            }
            if ($res = strcmp($aParents[$lvl]->getName(), $bParents[$lvl]->getName())) {
                return $res;
            }
        }
        return 0;
    }

    /**
     * Return the category name separator
     * 
     * @return string
     */
    public function getSeparator()
    {
        $separator = $this->_getData('separator');
        if (is_null($separator)) {
            $separator = Mage::getStoreConfig('vinaikopp_productcategories/general/category_separator');
            $this->setData('separator', $separator);
        }
        return $separator;
    }

    /**
     * Return css classes for the li tags
     * 
     * @param Mage_Catalog_Model_Category $cat
     * @return string
     */
    public function getListCssClasses(Mage_Catalog_Model_Category $cat)
    {
        $classes = array();
        if ($this->_firstProductCat) {
            if ($this->_firstProductCat->getId() == $cat->getId()) {
                $classes[] = 'first';
            }
        }
        if ($this->_lastProductCat) {
            if ($this->_lastProductCat->getId() == $cat->getId()) {
                $classes[] = 'last';
            }
        }
        return implode(' ', $classes);
    }

    /**
     * Return css classes for the span tags surrounding the category names
     * 
     * @param Mage_Catalog_Model_Category $cat
     * @return string
     */
    public function getCatCssClasses(Mage_Catalog_Model_Category $cat)
    {
        $classes = array();
        if ($cat->getIsBase()) {
            $classes[] = 'base';
        }
        if ($cat->getIsHead()) {
            $classes[] = 'head';
        }
        //if (! $classes) { ;)
        //    $classes[] = 'no-head-or-tails';
        //}
        return implode(' ', $classes);
    }
}