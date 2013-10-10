<?php


class VinaiKopp_ProductCategories_Block_CategoryList extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Set a default template
     */
    protected function _construct()
    {
        $this->setTemplate('vinaikopp/productcategories/list.phtml');
        parent::_construct();
    }

    /**
     * Return all categories associated with product
     * 
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCategories()
    {
        $categories = $this->getData('categories');
        if (is_null($categories)) {
            $categories = $this->getProduct()->getCategoryCollection()
                ->addIsActiveFilter()
                ->addNameToResult()
                ->addUrlRewriteToResult();
            $this->setCategories($categories);
        }
        return $categories;
    }

    /**
     * Return all categories associated with current product + parent categories
     * 
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getAllParentCategories()
    {
        $allParentCategories = $this->getData('all_parent_categories');
        if (is_null($allParentCategories)) {
            $ids = $categories = array();
            foreach ($this->getCategories() as $category) {
                /** @var Mage_Catalog_Model_Category $category */
                $ids = array_merge($ids, $category->getParentIds());
            }
            
            /** @var Mage_Catalog_Model_Resource_Category_Collection $allParentCategories */
            $allParentCategories = Mage::getModel('catalog/category')
                ->getCollection()
                ->addIdFilter(array_unique($ids, SORT_NUMERIC))
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
        $parents = array();
        foreach ($category->getPathIds() as $parentId) {
            if ($parent = $this->getAllParentCategories()->getItemById($parentId)) {
                if (empty($parents)) {
                    $parent->setIsLast(true);
                }
                $parents[] = $parent;
            }
        }
        return array_reverse($parents);
    }
}