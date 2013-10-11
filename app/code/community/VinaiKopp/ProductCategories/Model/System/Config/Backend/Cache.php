<?php


class VinaiKopp_ProductCategories_Model_System_Config_Backend_Cache
    extends Mage_Adminhtml_Model_System_Config_Backend_Cache
{
    protected $_cacheTags = array(
        VinaiKopp_ProductCategories_Block_CategoryList::CACHE_TAG
    );
}