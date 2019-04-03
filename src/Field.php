<?php namespace Dtkahl\FormBuilder;

class Field extends AbstractField
{
    protected $value = null;

    /**
     * @param $value
     * @return Field
     */
    protected function fromValue($value) : self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param $default
     * @return mixed
     */
    protected function toValue($default)
    {
        return $this->value === null ? $default : $this->value;
    }

}