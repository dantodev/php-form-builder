<?php namespace Dtkahl\FormBuilder;

use Dtkahl\ArrayTools\Map;

abstract class MapField extends AbstractField implements \ArrayAccess
{

    /** @var Map|AbstractField[] */
    protected $children;

    public function __construct(array $options = [])
    {
        $this->children = new Map;
        parent::__construct($options);
    }

    /**
     * @param string $name
     * @param null|AbstractField $child
     * @return AbstractField
     */
    protected function setChild(string $name, ?AbstractField $child = null) : AbstractField
    {
        if ($child === null) {
            $child = new Field;
        }
        $this->children->set($name, $child);
        $child->setName($name);
        $child->setParent($this);
        if ($child->options()->get('options_heredity', true)) { // TODO doc
            $child->options()->merge($this->options(), true);
        }
        return $child;
    }

    /**
     * @param string $name
     * @return $this|self
     */
    protected function removeChild(string $name) : self
    {
        $this->children->remove($name);
        return $this;
    }

    /**
     * @param $name
     * @return AbstractField
     */
    public function getChild(string $name) : AbstractField
    {
        $child = $this->children->get($name);
        if ($child instanceof AbstractField) {
            return $child;
        }
        throw new \RuntimeException("Unknown field '$name'.");
    }

    /**
     * @param mixed|null $default
     * @return array
     */
    public function toValue($default = null) : array
    {
        return $this->children->copy()->map(function ($name, $child) {
            /** @var AbstractField $child */
            return $child->getValue();
        })->toArray();
    }

    /**
     * @param array $data
     * @return void
     */
    protected function fromValue($data) : void
    {
        $data = (array) $data;
        foreach ($data as $name=>$field_data) {
            $this->getChild($name)->setValue($field_data);
        }
    }

    /**
     * @return array|bool
     */
    public function validate()
    {
        $this->valid = $this->children->copy()->filter(function (string $name, AbstractField $child) {
                return !$child->validate();
            })->count() == 0 && parent::validate();
        return $this->valid;
    }


    /**
     * @return array
     */
    public function getMessages() : array
    {
        $messages = [];
        foreach ($this->children->toArray() as $name=>$child) {
            /** @var AbstractField $child */
             if (!$child->isValid()) {
                 $messages[$name] = $child->getMessages();
             }
        }
        return $messages;
    }

    /**
     * @param string $offset
     * @return AbstractField
     */
    public function offsetGet($offset)
    {
        return $this->getChild($offset);
    }

    /**
     * @param string $offset
     * @param AbstractField $value
     */
    public function offsetSet($offset, $value)
    {
        $this->setChild($offset, $value);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->children->has($offset);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->removeChild($offset);
    }

}