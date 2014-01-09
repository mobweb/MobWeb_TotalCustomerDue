<?php

class MobWeb_TotalCustomerDue_Model_Resource_Sale_Collection extends Mage_Sales_Model_Resource_Sale_Collection
{
    public function __construct()
    {
        // Update protected $_totals = array( ...
        $this->_totals['due'] = 0;

        parent::__construct();
    }

    protected function _beforeLoad()
    {
        $this->getSelect()
            ->from(
                array('sales' => Mage::getResourceSingleton('sales/order')->getMainTable()),
                array(
                    'store_id',
                    'lifetime'      => new Zend_Db_Expr('SUM(sales.base_grand_total)'),
                    'due'           => new Zend_Db_Expr('SUM(sales.base_total_due)'),
                    'base_lifetime' => new Zend_Db_Expr('SUM(sales.base_grand_total * sales.base_to_global_rate)'),
                    'avgsale'       => new Zend_Db_Expr('AVG(sales.base_grand_total)'),
                    'base_avgsale'  => new Zend_Db_Expr('AVG(sales.base_grand_total * sales.base_to_global_rate)'),
                    'num_orders'    => new Zend_Db_Expr('COUNT(sales.base_grand_total)')
                )
            )
            ->group('sales.store_id');

        if ($this->_customer instanceof Mage_Customer_Model_Customer) {
            $this->addFieldToFilter('sales.customer_id', $this->_customer->getId());
        }

        if (!is_null($this->_orderStateValue)) {
            $condition = '';
            switch ($this->_orderStateCondition) {
                case 'IN' : 
                    $condition = 'in';
                    break;
                case 'NOT IN' : 
                    $condition = 'nin';
                    break;
            }
            $this->addFieldToFilter('state', array($condition => $this->_orderStateValue));
        }

        Mage::dispatchEvent('sales_sale_collection_query_before', array('collection' => $this));
        return $this;
    }
}