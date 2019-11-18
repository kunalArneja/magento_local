<?php
/**
 * Shipping Address Data Transformer Model
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Data\Transformer\Address;

use Narvar\ConnectEE\Model\Data\Transformer\AbstractTransformer;
use Narvar\ConnectEE\Model\Data\Transformer\TransformerInterface;
use Narvar\ConnectEE\Model\Data\DTO;

class Shipping extends AbstractTransformer implements TransformerInterface
{

    /**
     * Method to transform the Shipping Address in Required API Format
     *
     * @see \Narvar\ConnectEE\Model\Data\Transformer\TransformerInterface::transform()
     */
    public function transform(DTO $dto)
    {
        return [
            'shipped_to' => $this->prepareAddressInfo($dto->getOrder()->getShippingAddress())
        ];
    }
}
