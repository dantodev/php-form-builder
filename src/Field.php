<?php namespace Dtkahl\FormBuilder;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

class Field implements TwigRenderableInterface
{

    protected $name = null;
    protected $value = null;
    protected $label = null;
    protected $template = null;
    protected $validator = null;
    protected $messages = [];
    protected $valid = true;

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return null
     */
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

    /**
     * @return $this
     */
    public function resetValidation()
    {
        $this->messages = [];
        $this->validator = null;
        $this->valid = true;
        return $this;
    }

    /**
     * @param Validator $validator
     * @return $this
     */
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * @return null|Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        $this->valid = true;
        if ($this->validator instanceof Validator) {
            $this->validator->setName($this->getLabel() ?: $this->getName());
            try {
                $this->validator->assert($this->getValue());
            } catch (NestedValidationException $e) {
//                $e->setParams($this->params); // TODO
                $this->messages = $e->getMessages();
                $this->valid = false;
            }
        }
        return $this->valid;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    public function getTemplate(): string
    {
        if (is_null($this->template)) {
            throw new \RuntimeException("No template specified");
        }
        return $this->template;
    }

    public function getRenderData(array $data = []): array
    {
        return array_merge([
            "field" => $this,
            "name" => $this->name,
            "label" => $this->label,
            "valid" => $this->valid,
            "messages" => $this->messages,
        ], $data);    }

}