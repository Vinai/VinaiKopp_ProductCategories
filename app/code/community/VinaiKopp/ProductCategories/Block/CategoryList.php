<?php


class VinaiKopp_ProductCategories_Block_CategoryList extends Mage_Catalog_Block_Product_Abstract
{
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
     * Return all categories associated with product
     * 
     * @return Mage_Catalog_Model_Category[]
     */
    public function getCategories()
    {
        $categories = $this->getData('categories');
        if (is_null($categories)) {
            $categories = $this->getProduct()->getCategoryCollection()
                ->addIsActiveFilter()
                ->addNameToResult()
                ->addUrlRewriteToResult()->getItems();

            // Set to avoid recursion because getAllParentCategories() is called
            // sorting, which in turn calls getCategories() again.
            $this->setCategories($categories);
            
            usort($categories, array($this, '_sortByParentBaseCategory'));
            // Set the sorted categories for use by template
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
        if (! isset($this->_parents[$category->getId()])) {
            $parents = array();
            foreach ($category->getPathIds() as $parentId) {
                if ($parent = $this->getAllParentCategories()->getItemById($parentId)) {
                    $parents[] = $parent;
                }
            }
            if (isset($parent)) {
                $parent->setIsLast(true);
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
}