<?php namespace Dtkahl\FormBuilder;

use Dtkahl\ArrayTools\Map;
use Respect\Validation\Validator;

abstract class FieldSet implements \ArrayAccess, TwigRenderableInterface
{
    // TODO if Field/FieldSet implement the same interface it could be possible to put them into on Map without pain

    /** @var null|FieldSet */
    protected $parent;

    /** @var Map|Field[] */
    protected $fields;

    /** @var  Map|FieldSet[] */
    protected $field_sets;

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
        $this->fields = new Map;
        $this->field_sets = new Map;
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
        $this->fields->each(function (string $name, Field $field) use ($params) {
            $field->setValidationParams($params);
        });
        $this->field_sets->each(function (string $name, FieldSet $field_set) use ($params) {
            $field_set->setValidationParams($params);
        });
        return $this;
    }

    /**
     * @param string $name
     * @param Field $field
     * @return Field
     */
    protected function setField(string $name, Field $field) : FIeld
    {
        $this->removeFieldSet($name); // because name must be unique
        $this->fields->set($name, $field);
        $field->setName($name);
        $field->setParent($this);
        $field->setValidationParams($this->validation_params);
        return $field;
    }

    /**
     * @param string $name
     * @return $this|self
     */
    protected function removeField(string $name) : self
    {
        $this->fields->remove($name);
        return $this;
    }

    /**
     * @param $name
     * @return Field
     */
    public function getField(string $name) : Field
    {
        $field = $this->fields->get($name);
        if ($field instanceof Field) {
            return $field;
        }
        throw new \RuntimeException("Unknown field '$name'.");
    }

    /**
     * @param $name
     * @param FieldSet $field_set
     * @return FieldSet
     */
    public function setFieldSet(string $name, FieldSet $field_set) : FieldSet
    {
        $this->removeField($name); // because name must be unique
        $this->field_sets->set($name, $field_set);
        $field_set->setName($name);
        $field_set->setParent($this);
        $field_set->setValidationParams($this->validation_params);
        return $field_set;
    }

    /**
     * @param string $name
     * @return $this|self
     */
    protected function removeFieldSet(string $name) : self
    {
        $this->field_sets->remove($name);
        return $this;
    }

    /**
     * @param string $name
     * @return FieldSet
     */
    public function getFieldSet(string $name) : FieldSet
    {
        $field_set = $this->field_sets->get($name);
        if ($field_set instanceof FieldSet) {
            return $field_set;
        }
        throw new \RuntimeException("Unknown field set '$name'.");
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
     * @param string $name
     * @return mixed
     */
    public function getValue(string $name)
    {
        return $this->getField($name)->getValue();
    }

    /**
     * @return array
     */
    public function getValues() : array
    {
        $values = [];

        foreach ($this->fields->toArray() as $name=>$field) {
            /** @var Field $field */
            $values[$name] = $field->getValue();
        }
        foreach ($this->field_sets->toArray() as $name=>$field_set) {
            /** @var FieldSet $field_set */
            $values[$name] = $field_set->getValues();
        }

        return $values;
    }

    /**
     * @param $name
     * @param Validator $validator
     * @return self|$this
     */
    public function setValidator(string $name, ?Validator $validator) : self
    {
        $this->getField($name)->setValidator($validator);
        return $this;
    }

    /**
     * @param $name
     * @return Validator
     */
    public function getValidator(string $name) : ?Validator
    {
        return $this->getField($name)->getValidator();
    }

    /**
     * @param array $data
     * @return void
     */
    public function hydrate(array $data) : void
    {
        foreach ($data as $name=>$field_data) {
            $field = $this->fields->get($name);
            if ($field instanceof Field) {
                $field->setValue($field_data);
                continue;
            }
            $field_set = $this->field_sets->get($name);
            if ($field_set instanceof FieldSet) {
                $field_set->hydrate($field_data);
            }
        }
    }

    /**
     * @internal
     */
    public function initValidation() : void
    {
        $this->fields->each(function (string $name, Field $field) {
            $field->resetValidation();
        });
        $this->field_sets->each(function (string $name, FieldSet $field_set) {
            $field_set->initValidation();
            $this->setUpValidators();
        });
        $this->setUpValidators();
    }

    /**
     * @return bool
     */
    public function validate() : bool
    {
        $this->initValidation();

        $invalid_fields = $this->fields->copy()->filter(function (string $name, Field $field) {
            return !$field->validate();
        });
        $invalid_field_sets = $this->field_sets->copy()->filter(function (string $name, FieldSet $field_set) {
            return !$field_set->validate();
        });
        $this->valid = $invalid_fields->count() == 0 && $invalid_field_sets->count() == 0;
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
        foreach ($this->fields->toArray() as $name=>$field) {
            /** @var Field $field */
             if (!$field->isValid()) {
                 $messages[$name] = $field->getMessages();
             }
        }
        foreach ($this->field_sets->toArray() as $name=>$field_set) {
            /** @var FieldSet $field_set */
            if (!$field_set->isValid()) {
                $messages[$name] = $field_set->getMessages();
            }
        }
        return $messages;
    }

    public function offsetGet($offset)
    {
        if ($this->fields->has($offset)) {
            return $this->fields->get($offset);
        } elseif ($this->field_sets->has($offset)) {
            return $this->field_sets->get($offset);
        } else {
            return null;
        }
    }

    public function offsetSet($offset, $value)
    {
        if ($value instanceof Field) {
            $this->setField($offset, $value);
        } elseif ($value instanceof FieldSet) {
            $this->setFieldSet($offset, $value);
        } else {
            throw new \InvalidArgumentException("The Value must be an instance of Field or FieldSet");
        }
    }

    public function offsetExists($offset)
    {
        return $this->fields->has($offset) || $this->field_sets->has($offset);
    }

    public function offsetUnset($offset)
    {
        $this->removeField($offset);
        $this->removeFieldSet(
            $offset);
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
            "field_set" => $this,
            "name" => $this->name,
            "label" => $this->label,
            "valid" => $this->valid,
            "messages" => $this->messages,
        ], $data);
    }

}