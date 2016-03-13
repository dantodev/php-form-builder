<?php namespace Dtkahl\FormBuilder\Traits;

use Dtkahl\FormBuilder\FormBuilder;
use Dtkahl\FormBuilder\Interfaces\FormElementInterface;
use Dtkahl\FormBuilder\Interfaces\FormInterface;
use Dtkahl\ParameterTrait\ParameterTrait;

/**
 * @mixin FormElementInterface
 */
trait FormElementTrait
{
  use ParameterTrait;

  private $_name;
  private $_builder;
  private $_form;

  /**
   * FormElementTrait constructor.
   * @param string $name
   * @param FormBuilder $builder
   * @param FormInterface $form
   * @param array $properties
   */
  public function __construct(string $name, FormBuilder $builder, FormInterface $form,  array $properties = [])
  {
    $this->_name = $name;
    $this->_builder = $builder;
    $this->_form = $form;
    $this->_properties = $properties;
  }

  public function getName()
  {
    return $this->_name;
  }

}