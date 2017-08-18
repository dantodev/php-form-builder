<?php namespace Dtkahl\FormBuilder;

use Dtkahl\ArrayTools\Map;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

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

    /** @var null|AbstractField */
    private $parent = null;

    /** @var \Closure|Validator|null */
    private $validator = null;

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
        $this->setUp();
    }

    /**
     * define the child fields here
     */
    abstract public function setUp() : void;

    public function setName(string $name)
    {
        if (!is_null($this->name) && $this->hasParent()) {
            throw new \RuntimeException("It is not allowed to change the name if a parent is defined.");
        }
        $this->name = $name;
    }

    public function getName(bool $with_path = false) : ?string
    {
        if (!$with_path) {
            return $this->name;
        }
        $parent = $this->getParent();
        $trace = [$this->name];
        while ($parent instanceof AbstractField) {
            if ($parent->hasParent()) {
                array_unshift($trace, $parent->getName());
            }
            $parent = $parent->getParent();
        }
        return array_shift($trace) . join('', array_map(function ($name) {return "[$name]";}, $trace));
    }

    public function options() : Map
    {
        return $this->options;
    }

    public function getOption($key, $default = null)
    {
        return $this->options->get($key, $default);
    }

    public function setOption($key, $value) : self
    {
        $this->options->set($key, $value);
        return $this;
    }

    public function setParent(AbstractField $parent)
    {
        if ($this->hasParent()) {
            throw new \RuntimeException("It is not allowed to change the parent if another is already defined.");
        }
        $this->parent = $parent;
    }

    public function getParent() : ?AbstractField
    {
        return $this->parent;
    }

    public function hasParent() : bool
    {
        return !is_null($this->parent);
    }

    public function setValidator($validator) : self
    {
        if (!$validator instanceof Validator && !is_callable($validator)) {
            throw new \InvalidArgumentException("Given \$validator must instance of \Respect\Validation\Validator or callable.");
        }
        $this->validator = $validator;
        return $this;
    }

    public function removeValidator() : self
    {
        $this->validator = null;
        return $this;
    }

    abstract protected function hydrate($data);

    abstract public function getValue();

    public function setValue($data) : self
    {
        $this->hydrate($data);
        $this->messages = [];
        return $this;
    }

    public function validate()
    {
        $this->valid = true;
        $validator = $this->validator;
        if ($validator instanceof \Closure) {
            $validator = $validator($this); // TODO doco
        }
        if ($validator instanceof Validator) {
            $validator->setName($this->options()->get('label', $this->getName()));
            try {
                $validator->assert($this->getValue());
            } catch (NestedValidationException $e) {
                $validation_params = $this->options()->get('validation_params', []);
                $e->setParams($validation_params);
                foreach ($e->getIterator() as $e2) {
                    $e2->setParams($validation_params);
                }
                $this->messages = $e->getMessages();
                $this->valid = false;
            }
        }
        return $this->valid;
    }

    public function isValid() : bool
    {
        return $this->valid;
    }

    public function getMessages() : array
    {
        return $this->messages;
    }

}