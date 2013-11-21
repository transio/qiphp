<?php
namespace Qi\Form;

use Enum\InputType;

/**
 * The HiddenInput class represents a hidden form input
 */
class HiddenInput extends Input
{
    /**
     * Constructor
     * @param $name String
     * @param $title String
     */
    public function __construct($name, $value, array $properties=null)
    {
        $properties["value"] = $value;
        parent::__construct(InputType::HIDDEN, $name, $properties);
    }
}
