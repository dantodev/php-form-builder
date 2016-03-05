<?php namespace Dtkahl\FormBuilder\Interfaces;

use Dtkahl\FormBuilder\FormBuilder;

interface FormElementInterface
{

  /**
   * FormElementInterface constructor.
   * @param FormBuilder $builder
   * @param array $options
   */
  public function __construct(FormBuilder $builder, array $options = []);

  /**
   * @param string $mode
   * @return string|null
   */
  public function getView(string $mode);

  /**
   * @param string $mode
   * @return string
   */
  public function render(string $mode);

}