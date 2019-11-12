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

use Narvar\Connect\Model\Data\DTO;
use Narvar\Connect\Helper\Config\Locale as LocaleResolver;

class Locale implements TransformerInterface
{
    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    /**
     * Locale constructor.
     * @param LocaleResolver $localeResolver
     */
    public function __construct(
        LocaleResolver $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
    }

    public function transform(DTO $dto)
    {
        $storeId = $dto->getOrder()->getStoreId();
        $this->localeResolver->setStoreId($storeId);

        return [
            'locale' => $this->localeResolver->getLocale()
        ];
    }

}
