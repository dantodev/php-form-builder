<?php namespace Dtkahl\FormBuilder\Interfaces;

use Dtkahl\FormBuilder\FormBuilder;

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
   * @param string $mode
   * @return string|null
   */
  public function getView(string $mode);

  /**
   * @param string $mode
   * @return mixed
   */
  public function render(string $mode);

}