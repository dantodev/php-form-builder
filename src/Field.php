<?php namespace Dtkahl\FormBuilder;

class Field extends AbstractField
{
    protected $value = null;

    public function setUp(): void
    {}

    public function fromValue($value) : self
    {
        $this->value = $value;
        return $this;
    }

    public function toValue($default = null)
    {
        return $this->value ?: $default;
    }

}