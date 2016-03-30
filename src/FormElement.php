<?php namespace Dtkahl\FormBuilder;

use Dtkahl\FormBuilder\Interfaces\FormElementInterface;
use Dtkahl\ArrayTools\Map;

abstract class FormElement implements FormElementInterface
{

  protected $_name;
  protected $_builder;
  protected $_form;

  /** @var Map $properties */
  public $properties;

  /**
   * FormElementTrait constructor.
   * @param string $name
   * @param FormBuilder $builder
   * @param Form $form
   * @param array $properties
   */
  public function __construct($name, FormBuilder $builder, Form $form,  array $properties = [])
  {
    $this->_name = $name;
    $this->_builder = $builder;
    $this->_form = $form;
    $this->properties = new Map($properties);
  }

  public function getName()
  {
    return $this->_name;
  }

}