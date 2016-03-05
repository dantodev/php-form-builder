<?php namespace Dtkahl\FormBuilder\Interfaces;

use Dtkahl\FormBuilder\FormBuilder;
use Dtkahl\FormBuilder\Traits\FormTrait;

/**
 * @mixin FormTrait
 */
interface FormInterface
{

  public function __construct(FormBuilder $builder, array $options= []);

  /**
   * @param $name
   * @param string $element
   * @return FormInterface
   */
  public function addElement(string $name, string $element);

  /**
   * @return array
   */
  public function getElements();

  /**
   * @return string
   */
  public function render();

  // TODO save

}