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
   * @param FormBuilder $builder
   * @param FormInterface $form
   * @param array $data
   */
  public function __construct(FormBuilder $builder, FormInterface $form, array $data = []);

  /**
   * @return string
   */
  public function render();

}