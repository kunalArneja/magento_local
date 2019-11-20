<?php
/**
 * Config Custom Attributes Datetime Source Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\System\Config\Source\Attributes;

class Date extends \Narvar\Connect\Model\System\Config\Source\Attributes
{

    /**
     * Method to return Customer, Order, Address and Product Attributes
     * Which have datetime as input as Select Options
     *
     * @param boolean $isMultiSelect
     * @param array $filter
     * @return multitype
     */
    public function toOptionArray($isMultiSelect, $filter = [])
    {
        return parent::toOptionArray(
            $isMultiSelect,
            $filter = [
                'datetime_flag' => true
            ]
        );
    }
}
