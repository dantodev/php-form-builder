<?php namespace Dtkahl\FormBuilder\Interfaces;

use Dtkahl\FormBuilder\Form;
use Dtkahl\FormBuilder\FormBuilder;
interface FormElementInterface
{

  /**
   * FormElementInterface constructor.
   * @param string $name
   * @param FormBuilder $builder
   * @param Form $form
   * @param array $properties
   */
  public function __construct($name, FormBuilder $builder, Form $form, array $properties = []);

  /**
   * @return string
   */
  public function render();

  /**
   * @return FormElementInterface
   */
  public function save();

}