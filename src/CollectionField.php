<?php namespace Dtkahl\FormBuilder;

use Dtkahl\ArrayTools\Map;

class CollectionField extends AbstractField
{

    /** @var Map|AbstractField[] */
    protected $children;
    protected $child_class;
    protected $child_args;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->children = new Map();
        $this->child_class = $this->options()->get('child_class');
        $this->child_args = $this->options()->get('child_args', []);

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
    protected function fromValue($data)
    {
        $data = (array) $data;
        foreach ($data as $child_data) {
            $this->appendChild($child_data);
        }
    }

    /**
     * @param null $data
     * @return bool
     */
    public function validate($data = null)
    {
        $this->valid = $this->children->copy()->filter(function (string $name, AbstractField $child) use ($data) {
            return !$child->validate($data);
        })->count() == 0 && parent::validate($data);
        return $this->valid;
    }

    /**
     * @param array|null $args
     * @return AbstractField|CollectibleInterface
     */
    public function createChildClassInstance(?array $args = null) : AbstractField
    {
        $name = $this->child_class;
        $args = $args ?: $this->child_args;
        return new $name(...$args);
    }

    /**
     * @param array|CollectibleInterface $child_data
     * @return AbstractField
     */
    public function appendChild($child_data) : AbstractField
    {
        if ($child_data instanceof CollectibleInterface) {
            $child = $child_data;
        } else {
            $child = $this->createChildClassInstance();
        }
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
    protected function toValue($default) : array
    {
        return array_values($this->children->copy()->map(function ($name, AbstractField $child) {
            return $child->getValue();
        })->toArray());
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
     * @return array
     */
    public function toSerializedArray()
    {
        $data = parent::toSerializedArray();
        $data["collection"] = array_values($this->children->copy()->map(function ($name, AbstractField $child) {
            return $child->toSerializedArray();
        })->toArray());
        $data["new_config"] = $this->createChildClassInstance()->toSerializedArray();
        return $data;
    }

}