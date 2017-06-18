<?php namespace Dtkahl\FormBuilder;

use Dtkahl\ArrayTools\Map;

abstract class Form
{
    /** @var Map|FormElement[] */
    protected $fields = [];
    protected $properties;

    /**
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        $this->properties = new Map($properties);
        $this->fields = new Map();
        $this->setUp();
    }

    abstract public function setUp();

    /**
     * @param string $identifier
     * @param FormElement $element
     * @return $this
     */
    protected function addField($identifier, FormElement $element)
    {
        if (array_key_exists($identifier, $this->fields)) {
            throw new \RuntimeException("Form field with name '$identifier' already exists!");
        }
        $this->fields->set($identifier, $element);
        return $this;
    }

    /**
     * @return Map|FormElement[]
     */
    public function fields()
    {
        return $this->fields;
    }

    /**
     * @param $identifier
     * @return FormElement
     */
    public function field($identifier)
    {
        return $this->fields->get($identifier);
    }

    /**
     * @param array $data
     * @return mixed|void
     */
    public function hydrate(array $data)
    {
        foreach ($data as $identifier=>$field_data) {
            $field = $this->fields->get($identifier);
            if ($field instanceof FormElement) {
                $field->hydrate($field_data);
            }
        }
    }

    /**
     * @return mixed|void
     */
    public function render()
    {
        foreach ($this->fields as $field) {
            $field->render();
        }
    }

    /**
     * @return mixed|void
     */
    public function save()
    {
        foreach ($this->fields as $field) {
            $field->save();
        }
    }
}