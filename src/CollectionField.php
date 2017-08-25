<?php namespace Dtkahl\FormBuilder;

use Dtkahl\ArrayTools\Map;

class CollectionField extends AbstractField
{

    /** @var Map|AbstractField[] */
    protected $children;
    protected $child_class;
    protected $child_options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->children = new Map();
        $this->child_class = $this->options()->get('child_class');
        $this->child_options = $this->options()->get('child_options', []);

        if (is_null($this->child_class) || !is_subclass_of($this->child_class, AbstractField::class)) {
            throw new \InvalidArgumentException("'child_class' must be a sub class of AbstractField.");
        }
        if (!in_array(CollectibleInterface::class, class_implements($this->child_class, AbstractField::class) ?: [])) {
            throw new \InvalidArgumentException("'child_class' must implement CollectibleInterface.");
        }
    }

    /**
     * @param array $data
     */
    public function fromValue($data)
    {
        $data = (array) $data;
        foreach ($data as $child_data) {
            $this->appendChild($child_data);
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
     * @param array $child_data
     * @return AbstractField
     */
    public function appendChild(array $child_data) : AbstractField
    {
        $class_name = $this->child_class;
        /** @var AbstractField|CollectibleInterface $child */
        $child = new $class_name($this->child_options);
        $child->setValue($child_data);
        $identifier = $child->getUniqueIdentifier();
        $child->setName($identifier);
        $this->children->set($identifier, $child);
        return $child;
    }

    /**
     * @param $default
     * @return array
     */
    public function toValue($default) : array
    {
        return $this->children->copy()->map(function ($name, AbstractField $child) {
            return $child->getValue();
        })->toArray();
    }

    /**
     * @param $identifier
     * @return AbstractField
     */
    public function getChild($identifier) : AbstractField
    {
        $child = $this->children->get($identifier);
        if (!$child instanceof CollectibleInterface) {
            throw new \InvalidArgumentException("There is no child with identifier '$identifier' in this CollectionField.");
        }
        return $child;
    }

    /**
     * @return array
     */
    public function children() : array
    {
        return $this->children->toArray();
    }

    /**
     * @param bool $with_value
     * @return array
     */
    public function toSerializedArray(bool $with_value = false)
    {
        return array_values($this->children->copy()->map(function ($name, AbstractField $child) {
            return $child->toSerializedArray();
        })->toArray());
    }

}