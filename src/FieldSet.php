<?php namespace Dtkahl\FormBuilder;

use Dtkahl\ArrayTools\Map;
use Respect\Validation\Validator;

abstract class FieldSet implements \ArrayAccess, TwigRenderableInterface
{
    // TODO if Field/FieldSet implement the same interface it could be possible to put them into on Map without pain
    /** @var Map|Field[] */
    protected $fields;

    /** @var  Map|FieldSet[] */
    protected $field_sets;

    /** @var Validator[] */
    protected $validators;

    /** @var Map */
    protected $messages;

    /** @var array */
    protected $params = [];

    /** @var null */
    protected $template = null;

    /** @var null|string */
    protected $name = null;

    /** @var string|null */
    protected $label = null;

    /** @var bool */
    protected $valid = true;

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
     * @param string $name
     * @param Field $field
     * @return Field
     */
    protected function setField(string $name, Field $field)
    {
        $this->removeFieldSet($name); // because name must be unique
        $this->fields->set($name, $field);
        $field->setName($name);
        return $field;
    }

    /**
     * @param string $name
     */
    protected function removeField(string $name)
    {
        $this->fields->remove($name);
    }

    /**
     * @param $name
     * @return Field
     */
    public function getField(string $name)
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
    public function setFieldSet(string $name, FieldSet $field_set)
    {
        $this->removeField($name); // because name must be unique
        $this->field_sets->set($name, $field_set);
        $field_set->setName($name);
        return $field_set;
    }

    /**
     * @param string $name
     */
    protected function removeFieldSet(string $name)
    {
        $this->field_sets->remove($name);
    }

    /**
     * @param string $name
     * @return FieldSet
     */
    public function getFieldSet(string $name)
    {
        $field_set = $this->field_sets->get($name);
        if ($field_set instanceof FieldSet) {
            return $field_set;
        }
        throw new \RuntimeException("Unknown field set '$name'.");
    }

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
     * @return $this
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
        return $this;
    }

  /**
   * @return string
   */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getValue(string $name)
    {
        return $this->getField($name)->getValue();
    }

    /**
     * @param $name
     * @param Validator $validator
     * @return $this
     */
    public function setValidator(string $name, Validator $validator)
    {
        $this->getField($name)->setValidator($validator);
        return $this;
    }

    /**
     * @param $name
     * @return Validator
     */
    public function getValidator(string $name)
    {
        return $this->getField($name)->getValidator();
    }

    /**
     * @param array $data
     * @return mixed|void
     */
    public function hydrate(array $data)
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
    public function initValidation()
    {
        foreach ($this->fields as $field) {
            $field->resetValidation();
        }
        foreach ($this->field_sets as $field_set) {
            $field_set->initValidation();
            $this->setUpValidators();
        }
        $this->setUpValidators();
    }

    /**
     * @return bool
     */
    public function validate()
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
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @return array
     */
    public function getMessages()
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

    public function getTemplate(): string
    {
        if (is_null($this->template)) {
            throw new \RuntimeException("No template specified");
        }
        return $this->template;
    }

    public function getRenderData(array $data = []): array
    {
        return array_merge(["field_set" => $this], $data);
    }

}