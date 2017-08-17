<?php namespace Dtkahl\FormBuilder;

class Field extends AbstractField
{
    protected $value = null;

    public function setUp(): void
    {}

    public function hydrate($value) : self
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

}