<?php namespace Dtkahl\FormBuilder\Interfaces;

use Dtkahl\FormBuilder\FormBuilder;
use Dtkahl\FormBuilder\Traits\FormTrait;

/**
 * @mixin FormTrait
 */
interface FormInterface
{

  /**
   * FormInterface constructor.
   * @param string $name
   * @param FormBuilder $builder
   * @param array $properties
   */
  public function __construct($name, FormBuilder $builder, array $properties = []);

  /**
   * @param string $name
   * @param string $element
   * @param array $properties
   * @return mixed
   */
  public function registerElement($name, $element, array $properties = []);

  /**
   * @return FormElementInterface[]
   */
  public function getElements();

  /**
   * @return string
   */
  public function render();

  /**
   * @return FormInterface
   */
  public function save();

}