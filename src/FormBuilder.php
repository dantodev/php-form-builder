<?php namespace Dtkahl\FormBuilder;

use Dtkahl\FormBuilder\Interfaces\FormInterface;
use Dtkahl\FormBuilder\Traits\ParameterTrait;

class FormBuilder
{
  use ParameterTrait;

  private $_forms = [];

  /**
   * FormBuilder constructor.
   * @param array $parameter
   */
  public function __construct(array $parameter = [])
  {
    $this->_parameter = $parameter;
  }

  /**
   * @param string $name
   * @param string $class
   * @param array $options
   * @return FormInterface
   */
  public function registerForm(string $name, string $class, array $options = [])
  {
    if (array_key_exists($name, $this->_forms)) {
      throw new \RuntimeException("Form with name '$name' already registered!");
    }
    $this->_forms[$name] = new $class($this, $options);
    return $this->_forms[$name];
  }

  /**
   * @param string $name
   * @return FormInterface
   */
  public function getForm(string $name)
  {
    if (!array_key_exists($name, $this->_forms)) {
      throw new \RuntimeException("Form with name '$name' not registered!");
    }
    return $this->_forms[$name];
  }
}