<?php namespace Dtkahl\FormBuilder;

use Dtkahl\FormBuilder\Interfaces\FormInterface;
use Dtkahl\SimpleView\ViewRenderer;

class FormBuilder
{

  private $_renderer;
  private $_forms = [];

  public function __construct(ViewRenderer $renderer)
  {
    $this->_renderer = $renderer;
  }

  /**
   * @param string $name
   * @param string $class
   * @param array $options
   * @return mixed
   */
  public function register(string $name, string $class, array $options = [])
  {
    if (array_key_exists($name, $this->_forms)) {
      throw new \RuntimeException("Form with name '$name' already registered!");
    }
    $this->_forms[$name] = new $class($this, $options);
    return $this->_forms[$name];
  }

  /**
   * @param string $name
   * @return FormInterface
   */
  public function get(string $name)
  {
    if (!array_key_exists($name, $this->_forms)) {
      throw new \RuntimeException("Form with name '$name' not registered!");
    }
    return $this->_forms[$name];
  }

  /**
   * @param string $view
   * @param array $data
   * @return string
   */
  public function render(string $view, array $data) {
    return $this->_renderer->render($view, $data);
  }

}