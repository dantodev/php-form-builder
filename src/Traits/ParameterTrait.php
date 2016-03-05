<?php namespace Dtkahl\FormBuilder\Traits;

trait ParameterTrait
{

  private $_parameter = [];

  /**
   * @param string $key
   * @return bool
   */
  public function hasParameter(string $key)
  {
    return array_key_exists($key, $this->_parameter);
  }

  /**
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public function getParameter(string $key, mixed $default = null)
  {
    return $this->hasParameter($key) ? $this->_parameter[$key] : $default;
  }

  /**
   * @param string $key
   * @param mixed $parameter
   * @return $this
   */
  public function setParameter(string $key, mixed $parameter)
  {
    $this->_parameter[$key] = $parameter;
    return $this;
  }

}