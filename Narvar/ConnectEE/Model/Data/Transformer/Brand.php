<?php
/**
 * Order Data Transformer
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Model\Data\Transformer;

use Narvar\ConnectEE\Model\Data\DTO;

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
