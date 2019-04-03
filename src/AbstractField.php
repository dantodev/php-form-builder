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
    protected $options = [];

    /** @var \Closure|null */
    protected $validator = null;

    /** @var MapperInterface|null */
    protected $mapper = null;

    /** @var array|null */
    protected $messages = [];

    /** @var bool */
    protected $valid = true;


    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = (new Map($this->options))->merge($options);
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
     * @param MapperInterface $mapper
     * @return AbstractField
     */
    public function setMapper(MapperInterface $mapper) : self
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return AbstractField
     */
    public function unsetMapper() : self
    {
        $this->mapper = null;
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
        return $this->mapper instanceof MapperInterface ? $this->mapper->map($value) : $value;
    }

    public function getUnmappedValue($default = null)
    {
        return $this->toValue($default);
    }

    /**
     * @param $data
     * @return AbstractField
     */
    public function setValue($data) : self
    {
        $this->fromValue($this->mapper instanceof MapperInterface ? $this->mapper->unmap($data) : $data);
        $this->messages = [];
        return $this;
    }

    /**
     * @param null $data
     * @return bool
     */
    public function validate($data = null)
    {
        $this->valid = true;
        $validator = $this->validator;
        if (is_callable($validator)) {
            $messages = (array)$validator($this, $data);
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
     * @return array
     */
    public function toSerializedArray()
    {
        return [
            "name" => $this->getName(),
            "options" => $this->options()->toSerializedArray(),
            "valid" => $this->isValid(),
            "messages" => $this->getMessages(),
            "value" => $this->getValue()
        ];
    }

}