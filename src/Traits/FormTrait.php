<?php namespace Dtkahl\FormBuilder\Traits;

use Dtkahl\FormBuilder\FormBuilder;
use Dtkahl\FormBuilder\Interfaces\FormInterface;

/**
 * @mixin FormInterface
 */
trait FormTrait
{

  private $_builder;
  private $_options   = [];
  private $_elements  = [];

  /**
   * FormTrait constructor.
   * @param FormBuilder $builder
   * @param array $options
   */
  public function __construct(FormBuilder $builder, array $options = [])
  {
    $this->_builder = $builder;
    $this->_options = $options;
  }

  /**
   * @param string $name
   * @param string $element
   * @param array $options
   * @return $this
   */
  public function addElement(string $name, string $element, array $options = [])
  {
    $this->_elements[$name] = new $element($this->_builder, $options);
    return $this;
  }

  public function render(string $mode)
  {
    $view = $this->getView($mode);
    if ($view === null) {
      throw new \InvalidArgumentException("Unknown render mode '$mode' for form");
    }
    return $this->_builder->render($view, [
        'mode' => $mode,
        'elements' => $this->_elements,
        'options' => $this->_options,
    ]);
  }

}