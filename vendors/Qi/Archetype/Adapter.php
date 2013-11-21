<?php
namespace Qi\Archetype;
use Qi\Archetype\Enum\DataType;

/**
 * Base adapter class
 * @author Steven Moseley
 */
abstract class Adapter
{
    const KEY = 'key';
    const TYPE = 'type';
    const SEPARATOR = 'separator';
    const DEFAULT_VALUE = 'default';
    const PRECISION = 'precision';
    const VALUE_MAP = 'value_map';

    protected static $PATTERN = array();

    /**
     * Map values in
     * @static
     * @param array $in
     * @return array
     * @throws \Exception
     */
    public static function map(array $in)
    {
        // Create an output data-set
        $out = array();

        // Map the data
        foreach (static::$PATTERN as $out_key => $properties) {
            // If no key is set, use the out key
            if (!isset($properties[self::KEY])) {
                $properties[self::KEY] = $out_key;
                //throw new \Exception('Key is required.');
            }

            // Get the default value (if no item found in the input data-set)
            $default = isset($properties[self::DEFAULT_VALUE]) ? $properties[self::DEFAULT_VALUE] : null;

            // Get the value from the input data-set
            if (is_array($properties[self::KEY])) {
                // If it's an array of keys given, implode the applicable values
                // by the specified separator (or ',' if none is specified)
                $separator = isset($properties[self::SEPARATOR]) ? $properties[self::SEPARATOR] : ',';
                $value = array();
                foreach ($properties[self::KEY] as $key) {
                    if (isset($in[$key])) $value[] = $in[$key];
                }
                $value = implode($separator, $value);
            } else {
                // If a single key is given, set the value to the applicable data-set value
                $key = $properties[self::KEY];
                $value = isset($in[$key]) ? $in[$key] : $default;
            }

            // Get the data type (default is STRING)
            $type = isset($properties[self::TYPE]) ? $properties[self::TYPE] : DataType::STRING;

            // Cast types if a value is set
            if (!is_null($value)) {
                switch ($type) {
                    case DataType::STRING:
                        $value = (string) $value;
                        break;

                    case DataType::BOOLEAN:
                        $value = (bool) $value;
                        break;

                    case DataType::INTEGER:
                        $value = (int) $value;
                        break;

                    case DataType::DECIMAL:
                        $value = (double) $value;

                        // If a precision is specified, round the decimal
                        if (isset($properties[self::PRECISION])) {
                            $value = round($value, $properties[self::PRECISION]);
                        }
                        break;

                    case DataType::DATETIME:
                        $value = new DateTime($value);
                        break;
                }
            }

            // Map values if specified to do so
            // Unmap values if a value map exists
            $value_map = isset($properties[self::VALUE_MAP]) ? $properties[self::VALUE_MAP] : null;
            if (!empty($value_map)) {
                foreach ($value_map as $map => $val) {
                    if (is_array($val)) {
                        foreach ($val as $v) {
                            if ($value == $v) $value = $map;
                        }
                    } else {
                        if ($value == $val) $value = $map;
                    }
                }
            }

            // Add the final value to the output data-set
            $out[$out_key] = $value;
        }

        // Return the adapted data
        return $out;
    }

    /**
     * Map values out
     * @static
     * @param array $in
     * @return array
     * @throws \Exception
     */
    public static function unmap(array $in)
    {
        // Create an output data-set
        $out = array();

        // Map the data
        foreach (static::$PATTERN as $key => $properties) {
            // If no key is set, use the mapped key
            if (!isset($properties[self::KEY])) {
                $properties[self::KEY] = $key;
                //throw new \Exception('Key is required.');
            }

            // Get the default value (if no item found in the input data-set)
            $default = isset($properties[self::DEFAULT_VALUE]) ? $properties[self::DEFAULT_VALUE] : null;

            // Get the value from the input data-set
            $value = isset($in[$key]) ? $in[$key] : $default;

            // Unmap values if a value map exists
            $value_map = isset($properties[self::VALUE_MAP]) ? $properties[self::VALUE_MAP] : null;
            if (!empty($value_map)) {
                $value = isset($value_map[$value]) ? $value_map[$value] : null;
                if (is_array($value)) $value = $value[0];
            }

            if (is_array($properties[self::KEY])) {
                // If it's an array of keys given, set the value to all values in the array
                foreach ($properties[self::KEY] as $pk) {
                    $out[$pk] = $value;
                }
            } else {
                // If a single key is given, set the value to the applicable data-set value
                $out[$properties[self::KEY]] = $value;
            }

            // Add the final value to the output data-set
            $out[$key] = $value;
        }

        // Return the unmapped data
        return $out;
    }
}
