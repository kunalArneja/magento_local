<?php
/**
 * Formatter Helper
 *
 * @category    Narvar
 * @package     Narvar_ConnectEE
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\ConnectEE\Helper;

use Magento\Framework\Stdlib\DateTime as StdDateTime;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Narvar\ConnectEE\Helper\Config\Attribute as ConfigAttribute;
use Magento\Framework\Simplexml\Config as ConfigSimpleXML;
use Magento\Framework\Module\Dir as ModuleDir;
use Magento\Framework\Module\Dir\Reader as ModeleDirReader;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Formatter extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * API Field Set Group Order
     */
    const FIELDSET_ORDER = 'order';

    /**
     * API Field Set Group Customer
     */
    const FIELDSET_CUSTOMER = 'customer';

    /**
     * API Field Set Group Address
     */
    const FIELDSET_ADDRESS = 'address';

    /**
     * API Field Set Group Billing
     */
    const FIELDSET_BILLING = 'billing';

    /**
     * API Field Set Group Order Items
     */
    const FIELDSET_ORDERITEM = 'order_items';

    /**
     * API Field Set Group Shipments
     */
    const FIELDSET_SHIPMENT = 'shipments';

    /**
     * API Field Set Group Rma
     */
    const FIELDSET_RMA = 'rma';

    /**
     * API Field Set Group Rma
     */
    const FIELDSET_RMA_ITEMS = 'rma_items';

    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime;
     */
    private $dateTime;

    /**
     *
     * @var \Narvar\ConnectEE\Helper\Config\Attribute
     */
    private $configAttribute;

    /**
     *
     * @var \Magento\Framework\Simplexml\Config
     */
    private $configXml;

    /**
     *
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleDirReader;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Constructor
     *
     * @param DateTime $dateTime
     * @param ConfigAttribute $configAttribute
     * @param ModeleDirReader $moduleDirReader
     * @param ConfigSimpleXML $confiXml
     */
    public function __construct(
        DateTime $dateTime,
        ConfigAttribute $configAttribute,
        ModeleDirReader $moduleDirReader,
        ConfigSimpleXML $confiXml,
        TimezoneInterface $timezone
    ) {
        $this->dateTime = $dateTime;
        $this->configAttribute = $configAttribute;
        $this->moduleDirReader = $moduleDirReader;
        $this->configXml = $confiXml;
        $this->setFieldMapFile();
        $this->timezone = $timezone;
    }

    /**
     * Current Date
     *
     * @return return current date in GMT format
     */
    public function currentDate()
    {
        return $this->dateTime->date();
    }

    /**
     * Method to convert the datetime to ISO Format
     *
     * @param DateTime $dateTime
     * @return string
     */
    public function toISOFormat($dateTime)
    {
        $isoDateObj = $this->timezone->date($dateTime);
        
        return $isoDateObj->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Method to convert the given value in defined data type
     *
     * @param string $group
     * @param string $field
     * @param multitype $value
     * @return multitype
     */
    public function format($group, $field, $value)
    {
        $dataType = $this->getFieldDataType($group, $field);
        
        switch ($dataType) {
            case 'float':
                $returnValue = $this->toFloat($value);
                break;
            
            case 'boolean':
                $returnValue = $this->toBoolean($value);
                break;
            
            case 'date':
                $returnValue = $this->toString($value);
                break;
            
            case 'datetime':
                $returnValue = $this->toISOFormat($value);
                break;
            
            default:
                $returnValue = $this->toString($value);
        }
        
        return $returnValue;
    }

    /**
     * Method to verify given value is date or not
     * If value is date then convert it to required format
     * send it back as string
     *
     * @param string $value
     * @return string|Ambigous <string, unknown>
     */
    public function toString($value)
    {
        if ($value != '' && $this->isDate($value)) {
            return (string) $this->toDate($value);
        }
        
        return (string) ($value != '') ? $value : '';
    }

    /**
     * Method to convert the given value into Float
     *
     * @param multitype $number
     * @return float
     */
    public function toFloat($number)
    {
        return (float) number_format((float) $number, 2, '.', '');
    }

    /**
     * Method to return the value as boolean
     *
     * @param int $value
     * @return boolean
     */
    public function toBoolean($value)
    {
        return (bool) $value;
    }

    /**
     * Method to change given datetime/date into Y-m-d Format
     *
     * @param DateTime|Date $value
     * @return string
     */
    public function toDate($value)
    {
        return date(StdDateTime::DATE_PHP_FORMAT, strtotime($value));
    }

    /**
     * Method to check given value is date
     *
     * @param string $value
     * @return string
     */
    public function isDate($value)
    {
        return \DateTime::createFromFormat(StdDateTime::DATETIME_PHP_FORMAT, $value);
    }

    /**
     * Method to get Field data type form fieldmapper xml file
     *
     * @param string $group
     * @param string $field
     * @return string
     */
    public function getFieldDataType($group, $field)
    {
        if ($field === null) {
            return 'string';
        }
        
        $fieldPath = sprintf('%s/%s', $group, $field);
        
        return (string) $this->configXml->getNode($fieldPath)->dataType;
    }
    
    /**
     * Method to set the fieldMap Xml file as XML Object
     */
    public function setFieldMapFile()
    {
        $xmlPath = $this->moduleDirReader->getModuleDir(ModuleDir::MODULE_ETC_DIR, 'Narvar_ConnectEE') .
        "/field_mapper.xml";
        
        $this->configXml->loadFile($xmlPath);
    }
}
