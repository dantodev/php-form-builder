<?php namespace Dtkahl\FormBuilder\Traits;

use Dtkahl\FormBuilder\FormBuilder;
use Dtkahl\FormBuilder\Interfaces\FormInterface;

/**
 * @mixin FormInterface
 */
trait FormTrait
{
  use ParameterTrait;

  private $_builder;
  private $_elements  = [];

  /**
   * FormTrait constructor.
   * @param FormBuilder $builder
   * @param $parameter
   */
  public function __construct(FormBuilder $builder, array $parameter = [])
  {
    $this->_builder = $builder;
    $this->_parameter = $parameter;
  }

  /**
   * @param string $name
   * @param string $element
   * @param array $options
   * @return $this
   */
  public function addElement(string $name, string $element, array $options = [])
  {
    $this->_elements[$name] = new $element($this->_builder, $this, $options);
    return $this;
  }

  public function getElements()
  {
    return $this->_elements;
  }

}