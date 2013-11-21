<?php
namespace Qi\Form;

use Enum\InputType;

/**
 * The FileInput class represents file input form element
 */
class FileInput extends Input
{
    public function __construct($name, array $properties=null)
    {
        parent::__construct(InputType::FILE, $name, $properties);
    }
    
    public function getFile()
    {
        return File::loadUploadedFile($this->getName(), $this->filepath);
    }
}
