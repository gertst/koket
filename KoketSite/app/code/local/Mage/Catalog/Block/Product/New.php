<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gert
 * Date: 28/08/14
 * Time: 23:27
 * show all new products  - works together with a CMS page where this page is called using this Layout update XML:
 *  <reference name="content">
<block type="catalog/product_new" name="product_new" template="catalog/product/list.phtml">
<action method="setCategoryId"><category_id>3</category_id></action>
<action method="setColumnCount"><column_count>3</column_count></action>
<action method="setProductsCount"><count>0</count></action>
<block type="catalog/product_list_toolbar" name="product_list_toolbar" template="catalog/product/list/toolbar.phtml">
<block type="page/html_pager" name="product_list_toolbar_pager" />
<action method="setDefaultGridPerPage"><limit>30</limit></action>
<action method="addPagerLimit"><mode>grid</mode><limit>30</limit></action>
<action method="addPagerLimit"><mode>grid</mode><limit>60</limit></action>
<action method="addPagerLimit"><mode>grid</mode><limit>90</limit></action>
<action method="addPagerLimit" translate="label"><mode>grid</mode><limit>all</limit><label>All</label></action>
</block>
<!--<action method="addColumnCountLayoutDepend"><layout>one_column</layout><count>6</count></action>-->
<action method="setToolbarBlockName"><name>product_list_toolbar</name></action>
</block>
</reference>
 */


class Mage_Catalog_Block_Product_New extends Mage_Catalog_Block_Product_List
{
    function get_prod_count()
    {
        //unset any saved limits
        Mage::getSingleton('catalog/session')->unsLimitPage();
        return (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 12;
    }// get_prod_count

    function get_cur_page()
    {
        return (isset($_REQUEST['p'])) ? intval($_REQUEST['p']) : 1;
    }// get_cur_page

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     **/
    protected function _getProductCollection()
    {
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
            ->addAttributeToFilter('news_to_date', array('or'=> array(
                0 => array('date' => true, 'from' => $todayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToSort('news_from_date', 'desc')
            ->setPageSize($this->get_prod_count())
            ->setCurPage($this->get_cur_page());

        $this->setProductCollection($collection);

        return $collection;
    }// _getProductCollection
}// Mage_Catalog_Block_Product_New
?>
