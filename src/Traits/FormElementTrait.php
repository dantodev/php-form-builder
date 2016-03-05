<?php namespace Dtkahl\FormBuilder\Traits;

use Dtkahl\FormBuilder\FormBuilder;
use Dtkahl\FormBuilder\Interfaces\FormElementInterface;

/**
 * @mixin FormElementInterface
 */
trait FormElementTrait
{

  private $_builder;
  private $_options = [];

  /**
   * FormTrait constructor.
   * @param $builder
   * @param $options
   */
  public function __construct(FormBuilder $builder, array $options = [])
  {
    $this->_builder = $builder;
    $this->_options = $options;
  }

  /**
   * @param string $mode
   * @return string
   */
  public function render(string $mode)
  {
    $view = $this->getView($mode);
    if ($view === null) {
      throw new \InvalidArgumentException("Unknown render mode '$mode' for form");
    }
    return $this->_builder->render($view, $this->_options);
  }

}