<?php
/**
 * Returns Interface
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Returns;

interface ProcessInterface
{
    /**
     * Method to process the Return Request 
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $orderItems
     * @param \Narvar\ConnectEE\Model\Service\Response $narvarApiResponse
     * @param string|null $dateRequested
     */
    public function process(
        \Magento\Sales\Model\Order $order,
        $orderItems,
        \Narvar\ConnectEE\Model\Service\Response $narvarApiResponse,
        $dateRequested = null
    );
}