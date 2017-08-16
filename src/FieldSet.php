<?php namespace Dtkahl\FormBuilder;

use Dtkahl\ArrayTools\Map;
use Respect\Validation\Validator;

abstract class FieldSet implements \ArrayAccess
{

    /** @var null|FieldSet */
    protected $parent;

    /** @var Map|Field[] */
    protected $fields;

    /** @var  Map|FieldSet[]|Field[] */
    protected $children;

    /** @var Validator[] */
    protected $validators;

    /** @var Map */
    protected $messages;

    /** @var null */
    protected $template = null;

    /** @var null|string */
    protected $name = '';

    /** @var string|null */
    protected $label = '';

    /** @var bool */
    protected $valid = true;

    /** @var array */
    protected $validation_params = [];

    public function __construct()
    {
        $this->children = new Map;
        $this->setUp();
    }

    abstract public function setUp();

    /**
     * TODO docs
     * Set up the Field Validators here separately. This is only called when validation actually runs so you can use
     * field values as conditions.
     */
    abstract public function setUpValidators();

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
     * @param $params
     * @return $this|self
     */
    public function setValidationParams(array $params) : self
    {
        $this->validation_params = $params;
        $this->children->each(function (string $name, $child) use ($params) {
          /** @var Field|FieldSet $child */
          $child->setValidationParams($params);
        });
        return $this;
    }

    /**
     * @param string $name
     * @param Field|FieldSet $child
     * @return Field|FieldSet
     */
    protected function set(string $name, $child = null)
    {
        if ($child === null) {
          $child = new Field;
        }
        if (!$child instanceof Field && !$child instanceof FieldSet) {
          throw new \InvalidArgumentException("FormSet child must be null or instance of Field or FieldSet.");
        }
        $this->children->set($name, $child);
        $child->setName($name);
        $child->setParent($this);
        $child->setValidationParams($this->validation_params);
        return $child;
    }

    /**
     * @param string $name
     * @return $this|self
     */
    protected function remove(string $name) : self
    {
        $this->children->remove($name);
        return $this;
    }

    /**
     * @param $name
     * @return Field|FieldSet
     */
    public function get(string $name)
    {
        $field = $this->children->get($name);
        if ($field !== null) {
            return $field;
        }
        throw new \RuntimeException("Unknown field '$name'.");
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
        return $this->name;
    }

    /**
     * @param $label
     * @return $this|self
     */
    public function setLabel(string $label) : self
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
     * @param string|null $name
     * @return mixed
     */
    public function getValue(?string $name = null)
    {
        if ($name === null) {
            return $this->children->map(function ($name, $child) {
                /** @var Field|FieldSet $child */
                return $child->getValue();
            })->toArray();
        }
        return $this->get($name)->getValue();
    }

    /**
     * @param $name
     * @param Validator $validator
     * @return self|$this
     */
    public function setValidator(string $name, ?Validator $validator) : self
    {
        $child = $this->get($name);
        if ($child instanceof Field) {
            $child->setValidator($validator);
        }
        return $this;
    }

    /**
     * @param $name
     * @return Validator
     */
    public function getValidator(string $name) : ?Validator
    {
        $child = $this->get($name);
        if ($child instanceof Field) {
            $child->getValidator();
        }
        return null;
    }

    /**
     * @param array $data
     * @return void
     */
    public function hydrate(?array $data) : void
    {
        $data = (array) $data;
        foreach ($data as $name=>$field_data) {
            $child = $this->get($name);
            if ($child instanceof Field) {
                $child->setValue($field_data);
            } elseif ($child instanceof FieldSet) {
                $child->hydrate($field_data);
            }
        }
    }

    /**
     * @internal
     */
    public function initValidation() : void
    {
        $this->children->each(function (string $name, $child) {
            if ($child instanceof Field) {
                $child->resetValidation();
            } elseif ($child instanceof FieldSet) {
                $child->initValidation();
            }
        });
        $this->setUpValidators();
    }

    /**
     * @return bool
     */
    public function validate() : bool
    {
        $this->initValidation();

        $invalid_children = $this->children->copy()->filter(function (string $name, $child) {
            /** @var Field|FieldSet $child */
            return !$child->validate();
        });
        $this->valid = $invalid_children->count() == 0;
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
        $messages = [];
        foreach ($this->children->toArray() as $name=>$child) {
            /** @var Field|FieldSet $child */
             if (!$child->isValid()) {
                 $messages[$name] = $child->getMessages();
             }
        }
        return $messages;
    }

    public function offsetGet($offset)
    {
        if ($this->children->has($offset)) {
            return $this->children->get($offset);
        }
        return null;
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return $this->children->has($offset);
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

}