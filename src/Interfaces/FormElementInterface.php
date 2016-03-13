<?php namespace Dtkahl\FormBuilder\Interfaces;

use Dtkahl\FormBuilder\FormBuilder;
use Dtkahl\FormBuilder\Traits\FormElementTrait;

/**
 * @mixin FormElementTrait
 */
interface FormElementInterface
{

  /**
   * FormElementInterface constructor.
   * @param string $name
   * @param FormBuilder $builder
   * @param FormInterface $form
   * @param array $properties
   */
  public function __construct(string $name, FormBuilder $builder, FormInterface $form, array $properties = []);

  /**
   * @return string
   */
  public function render();

  /**
   * @return FormElementInterface
   */
  public function save();

}