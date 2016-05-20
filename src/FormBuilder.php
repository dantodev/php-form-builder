<?php namespace Dtkahl\FormBuilder;

use Dtkahl\FormBuilder\Interfaces\FormInterface;
use Dtkahl\ArrayTools\Map;

class FormBuilder
{

    private $_forms = [];

    /** @var Map $properties */
    public $properties;

    /**
     * FormBuilder constructor.
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        $this->properties = new Map($properties);
    }

    /**
     * @param string $name
     * @param string $class
     * @param array $properties
     * @return FormInterface
     */
    public function registerForm($name, $class, array $properties = [])
    {
        if (array_key_exists($name, $this->_forms)) {
            throw new \RuntimeException("Form with name '$name' already registered!");
        }
        $this->_forms[$name] = new $class($name, $this, $properties);
        return $this->_forms[$name];
    }

    /**
     * @param string $name
     * @return FormInterface
     */
    public function getForm($name)
    {
        if (!array_key_exists($name, $this->_forms)) {
            throw new \RuntimeException("Form with name '$name' not registered!");
        }
        return $this->_forms[$name];
    }
}