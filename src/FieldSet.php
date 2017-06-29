<?php namespace Dtkahl\FormBuilder;

use Dtkahl\ArrayTools\Map;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

// TODO custom messages/translations
abstract class FieldSet implements \ArrayAccess
{
    /** @var Map|Field[] */
    protected $fields;

    /** @var  Map|FieldSet[] */
    protected $field_sets;

    /** @var Validator[] */
    protected $validators;
    
    protected $messages;

    protected $params = [];

    public function __construct()
    {
        $this->fields = new Map;
        $this->field_sets = new Map;
        $this->validators = new Map;
        $this->messages = new Map;
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
        $this->messages->remove($name);
        $this->validators->remove($name);
    }

    /**
     * @param $name
     * @return Field
     */
    public function getField(string $name) // TODO array/dot notation to access sub field set fields
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
        return $field_set;
    }

    /**
     * @param string $name
     */
    protected function removeFieldSet(string $name)
    {
        $this->field_sets->remove($name);
        $this->messages->remove($name);
        $this->validators->remove($name);
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
     * @param $label
     * @return $this
     */
    public function setLabel(string $name, string $label)
    {
        $this->getField($name)->setLabel($label);
        return $this;
    }

  /**
   * @param string $name
   * @return string
   */
    public function getLabel(string $name)
    {
        return $this->getField($name)->getLabel();
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
        $this->validators->set($name, $validator);
        return $this;
    }

    /**
     * @param $name
     * @return Validator
     */
    public function getValidator(string $name)
    {
        return $this->validators->get($name);
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
     * @return bool
     */
    public function isValid()
    {
        // (re)initialize validators, allow conditions based on values
        // reset messages
        $this->validators = new Map;
        $this->messages = new Map;
        $this->setUpValidators();

        $invalid_fields = $this->fields->copy()->filter(function (string $name, Field $field) {
            $validator = $this->validators->get($name);
            if ($validator instanceof Validator) {
                $validator->setName($this->getLabel($name) ?: $name);
                try {
                    $validator->assert($field->getValue());
                } catch (NestedValidationException $e) {
                    $e->setParams($this->params); // TODO doc
                    $this->messages->set($name, $e->getMessages());
                    return true;
                }
            }
            return false;
        });
        $invalid_field_sets = $this->field_sets->copy()->filter(function (string $name, FieldSet $field_set) {
            if (!$field_set->isValid()) {
                $this->messages->set($name, $field_set->getMessages());
                return true;
            }
            return false;
        });
        return $invalid_fields->count() == 0 && $invalid_field_sets->count() == 0;
    }

    /**
     * @param $name
     * @return array
     */
    public function getMessages(string $name = null)
    {
        if (is_null($name)) {
            return $this->messages->toArray();
        }
        return $this->messages->get($name, []);
    }

    /**
     * @param string|null $name
     * @return bool
     */
    public function hasMessages(string $name = null)
    {
        if (is_null($name)) {
            return !$this->messages->isEmpty();
        }
        return $this->messages->has($name);
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
        $this->removeFieldSet($offset);
    }

}