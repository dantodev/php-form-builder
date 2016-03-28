<?php namespace Dtkahl\FormBuilder\Traits;

use Dtkahl\ArrayTools\Map;
use Dtkahl\FormBuilder\FormBuilder;
use Dtkahl\FormBuilder\Interfaces\FormElementInterface;
use Dtkahl\FormBuilder\Interfaces\FormInterface;

/**
 * @mixin FormInterface
 */
trait FormTrait
{

  private $_name;
  private $_builder;
  private $_elements = [];

  /** @var Map $properties */
  public $properties;

  /**
   * FormTrait constructor.
   * @param string $name
   * @param FormBuilder $builder
   * @param array $properties
   */
  public function __construct($name, FormBuilder $builder, array $properties = [])
  {
    $this->_name = $name;
    $this->_builder = $builder;
    $this->properties = new Map($properties);
  }

  /**
   * @param string $name
   * @param string $element
   * @param array $options
   * @return $this
   */
  public function registerElement($name, $element, array $options = [])
  {
    if (array_key_exists($name, $this->_elements)) {
      throw new \RuntimeException("Form element with name '$name' already registered!");
    }

    $this->_elements[$name] = new $element($name, $this->_builder, $this, $options);
    return $this;
  }

  /**
   * @return FormElementInterface[]
   */
  public function getElements()
  {
    return $this->_elements;
  }

  public function getName()
  {
    return $this->_name;
  }

}