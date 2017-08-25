<?php namespace Dtkahl\FormBuilder;

use Dtkahl\ArrayTools\Map;

/**
 * Class AbstractField
 * @package Dtkahl\FormBuilder
 */
abstract class AbstractField
{
    /** @var string */
    private $name;

    /** @var Map */
    private $options;

    /** @var \Closure|null */
    private $validator = null;

    /** @var \Closure|null */
    private $formatter = null;

    /** @var array|null */
    protected $messages = [];

    /** @var bool */
    protected $valid = true;


    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {

        $this->options = new Map($options);
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * @return Map
     */
    public function options() : Map
    {
        return $this->options;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getOption($key, $default = null)
    {
        return $this->options->get($key, $default);
    }

    /**
     * @param $key
     * @param $value
     * @return AbstractField
     */
    public function setOption($key, $value) : self
    {
        $this->options->set($key, $value);
        return $this;
    }

    /**
     * @param callable $validator
     * @return AbstractField
     */
    public function setValidator(callable $validator) : self
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * @return AbstractField
     */
    public function removeValidator() : self
    {
        $this->validator = null;
        return $this;
    }

    /**
     * @param callable $formatter
     * @return AbstractField
     */
    public function setFormatter(callable $formatter) : self
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * @return AbstractField
     */
    public function removeFormatter() : self
    {
        $this->formatter = null;
        return $this;
    }

    /**
     * @param $value
     */
    abstract protected function fromValue($value);

    /**
     * @param $default
     * @return mixed
     */
    abstract protected function toValue($default);

    /**
     * @param null $default
     * @return mixed
     */
    public function getValue($default = null)
    {
        $value = $this->toValue($default);
        $formatter = $this->formatter;
        if (is_callable($formatter)) {
            $value = $formatter($value);
        }
        return $value;
    }

    /**
     * @param $data
     * @return AbstractField
     */
    public function setValue($data) : self
    {
        $this->fromValue($data);
        $this->messages = [];
        return $this;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        $this->valid = true;
        $validator = $this->validator;
        if (is_callable($validator)) {
            $messages = (array)$validator($this);
            $this->messages = $messages;
            $this->valid = empty($messages);
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

    /**
     * @return array
     */
    public function getMessages() : array
    {
        return $this->messages;
    }

    /**
     * @param bool $with_value
     * @return array
     */
    public function toSerializedArray(bool $with_value = false)
    {
        $data = [
            "name" => $this->getName(),
            "options" => $this->options()->toSerializedArray()
        ];

        if ($with_value) {
            $data["messages"] = $this->getMessages();
            $data["valid"] = $this->isValid();
            $data["value"] = $this->getValue();
        }

        return $data;
    }

}