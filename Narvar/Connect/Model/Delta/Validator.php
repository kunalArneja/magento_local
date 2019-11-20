<?php
/**
 * Delta Validator Model
 *
 * @category    Narvar
 * @package     Narvar_Connect
 *
 * @author      premkumarsankar premkumar.sankar@aspiresys.com
 * @copyright   Copyright (c) 2012-2019 Narvar Inc
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Narvar\Connect\Model\Delta;

class Validator
{

    /**
     * Constant value for data type boolean
     */
    const DATA_TYPE_BOOL = 'boolean';

    /**
     * Constant value for data type integer
     */
    const DATA_TYPE_INT = 'integer';

    /**
     * Constant value for data type double
     */
    const DATA_TYPE_DOUBLE = 'double';

    /**
     * Constant value for data type string
     */
    const DATA_TYPE_STRING = 'string';

    /**
     * Constant value for data type array
     */
    const DATA_TYPE_ARRAY = 'array';

    /**
     * Constant value for data type object
     */
    const DATA_TYPE_OBJECT = 'object';

    /**
     * Method to check verfify type of data then validate values are same or changed
     *
     * @param multitype $oldData
     * @param multitype $newData
     * @param array $excludeFields
     * @return boolean
     */
    public function isIdentical($oldData, $newData, $excludeFields = [])
    {
        $oldValueDataType = gettype($oldData);
        $newValueDataType = gettype($newData);
        
        if ($oldValueDataType !== $newValueDataType) {
            return false;
        }
        
        $flag = true;
        
        switch ($oldValueDataType) {
            case self::DATA_TYPE_BOOL:
            case self::DATA_TYPE_DOUBLE:
            case self::DATA_TYPE_STRING:
            case self::DATA_TYPE_INT:
                $flag = $this->checkValuesAreSame($oldData, $newData);
                break;
            
            case self::DATA_TYPE_ARRAY:
                $flag = $this->checkArraysAreIdentical($oldData, $newData, $excludeFields);
                break;
            
            case self::DATA_TYPE_OBJECT:
                $flag = $this->checkArraysAreIdentical((array) $oldData, (array) $newData, $excludeFields);
                break;
            default:
                $flag = true;
        }
        
        return $flag;
    }

    /**
     * Method to check give value is same or not
     *
     * @param multitype $oldValue
     * @param multitype $newValue
     * @return boolean
     */
    private function checkValuesAreSame($oldValue, $newValue)
    {
        return $oldValue === $newValue ? true : false;
    }

    /**
     * Method to verify arrays are Same or changed
     *
     * @param array $oldData
     * @param array $newData
     * @return boolean
     */
    private function checkArraysAreIdentical($oldData, $newData, $excludeFields)
    {
        $count = count($oldData);

        if (count($newData) !== $count) {
            return false;
        }
        
        $arrKeysInCommon = array_intersect_key($oldData, $newData);
        if (count($arrKeysInCommon) !== $count) {
            return false;
        }

        $oldDataKeys = array_keys($oldData);
        $newDataKeys = array_keys($newData);
        foreach ($oldDataKeys as $key => $val) {
            if ($oldDataKeys[$key] !== $newDataKeys[$key]) {
                return false;
            }
        }

        foreach ($oldData as $key => $val) {
            if (! in_array($key, $excludeFields)) {
                if ($this->isIdentical($oldData[$key], $newData[$key]) === false) {
                    return false;
                }
            }
        }
        
        return true;
    }
}
