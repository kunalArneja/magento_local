<?php
/**
 * Order Data Transformer
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Data\Transformer;

use Narvar\Connect\Model\Data\Transformer\AbstractTransformer;
use Narvar\Connect\Model\Data\Transformer\TransformerInterface;
use Narvar\Connect\Model\Data\DTO;
use Narvar\Connect\Helper\Config\Attribute as AttributeHelper;
use Narvar\Connect\Helper\Formatter;
use Magento\Sales\Model\Order as OrderModel;

class Brand extends AbstractTransformer implements TransformerInterface
{
    /**
     * Method to prepare the Brand (Store code)
     *
     * @see Narvar_Connect_Model_Data_Transformer_Interface::transform()
     */
    public function transform(DTO $dto)
    {
        return [
            'brand' => $dto->getOrder()->getStore()->getCode()
        ];
    }

}
