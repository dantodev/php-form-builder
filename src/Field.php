<?php namespace Dtkahl\FormBuilder;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

class Field implements TwigRenderableInterface
{

    /** @var null|FieldSet */
    protected $parent;

    /** @var string */
    protected $name = '';

    /** @var null */
    protected $value = null;

    /** @var string */
    protected $label = '';

    /** @var null */
    protected $template = null;

    /** @var null|Validator */
    protected $validator = null;

    /** @var array */
    protected $messages = [];

    /** @var bool */
    protected $valid = true;

    /** @var array */
    protected $validation_params = [];

    /**
     * @param $params
     * @return $this|self
     */
    public function setValidationParams(array $params) : self
    {
        $this->validation_params = $params;
        return $this;
    }

    /**
     * @param FieldSet|null $field_set
     * @return $this|self
     */
    public function setParent(?FieldSet $field_set) : self
    {
        $this->parent = $field_set;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasParent() : bool
    {
        return !is_null($this->parent);
    }

    /**
     * @return FieldSet|null
     */
    public function getParent() : ?FieldSet
    {
        return $this->parent;
    }

    /**
     * @param $name
     * @return $this|self
     */
    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getName() : string
    {
        $parent = $this->getParent();
        $trace = [$this->name];
        while ($parent instanceof FieldSet) {
            if ($parent->hasParent()) {
                array_unshift($trace, $parent->getName());
            }
            $parent = $parent->getParent();
        }
        return array_shift($trace) . join('', array_map(function ($name) {return "[$name]";}, $trace));
    }

    /**
     * @param $label
     * @return $this|self
     */
    public function setLabel(string $label) : Field
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @param mixed $value
     * @return $this|self
     */
    public function setValue($value) : self
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

    /**
     * @return $this|self
     */
    public function resetValidation() : self
    {
        $this->messages = [];
        $this->validator = null;
        $this->valid = true;
        return $this;
    }

    /**
     * @param Validator $validator
     * @return $this|self
     */
    public function setValidator(Validator $validator) : self
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * @return null|Validator
     */
    public function getValidator() : ?Validator
    {
        return $this->validator;
    }

    /**
     * @return array
     */
    public function getMessages() : array
    {
        return $this->messages;
    }

    /**
     * @return bool
     */
    public function validate() : bool
    {
        $this->valid = true;
        if ($this->validator instanceof Validator) {
            $this->validator->setName($this->getLabel() ?: $this->getName());
            try {
                $this->validator->assert($this->getValue());
            } catch (NestedValidationException $e) {
                $e->setParams($this->validation_params);
                $this->messages = $e->getMessages();
                $this->valid = false;
            }
        }
        return $this->valid;
    }

    /**
     * @return bool
     */
    public function isValid() : bool
    {
        return $this->valid;
    }

    public function getTemplate() : string
    {
        if (is_null($this->template)) {
            throw new \RuntimeException("No template specified");
        }
        return $this->template;
    }

    public function getRenderData(array $data = []) : array
    {
        return array_merge([
            "field" => $this,
            "name" => $this->name,
            "label" => $this->label,
            "value" => $this->value,
            "valid" => $this->valid,
            "messages" => $this->messages,
        ], $data);
    }

}