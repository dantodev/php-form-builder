<?php namespace Dtkahl\FormBuilder;

class Field implements TwigRenderableInterface
{

    protected $name = null;
    protected $value = null;
    protected $label = null;
    protected $template = null;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getTemplate(): string
    {
        if (is_null($this->template)) {
            throw new \RuntimeException("No template specified");
        }
        return $this->template;
    }

    public function getRenderData(): array
    {
        return ["form" => $this];
    }

}