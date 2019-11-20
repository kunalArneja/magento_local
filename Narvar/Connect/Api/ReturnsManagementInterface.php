<?php
/**
 * Returns Management Interface
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Api;

interface ReturnsManagementInterface
{
    /**
     * Method to create the Return request
     *
     * @param string $orderNumber
     * @param string $dateRequested
     * @param \Narvar\Connect\Api\Data\ReturnsItemsInterface[] $orderItems
     * @return mixed
     */
    public function createReturn($orderNumber, $dateRequested, $orderItems);
}
