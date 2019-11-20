<?php
/**
 * Address Location Transformer Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Data\Transformer\Address;

use Narvar\Connect\Model\Data\Transformer\AbstractTransformer;
use Narvar\Connect\Model\Data\Transformer\TransformerInterface;
use Narvar\Connect\Model\Data\DTO;

class Location extends AbstractTransformer implements TransformerInterface
{

    /**
     * Method to transform the Address in Required API Format
     *
     * @see \Narvar\Connect\Model\Data\Transformer\TransformerInterface::transform()
     */
    public function transform(DTO $dto)
    {
        return $this->prepareAddressLocationInfo($dto->getOrder()->getBillingAddress());
    }
}
