<?php namespace Dtkahl\FormBuilder;

use Dtkahl\ArrayTools\Map;

class CollectionField extends AbstractField
{

    /** @var Map|AbstractField[] */
    protected $children;
    protected $child_class;
    protected $child_options;

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

    public function setUp() : void
    {}

    public function hydrate($data)
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

    public function appendChild(array $child_data) : AbstractField
    {
        $class_name = $this->child_class;
        /** @var AbstractField|CollectibleInterface $child */
        $child = new $class_name($this->child_options);
        if ($child->options()->get('options_heredity', true)) { // TODO doc
            $child->options()->merge($this->options(), true);
        }
        $child->setParent($this);
        $child->hydrate($child_data);
        $identifier = $child->getUniqueIdentifier();
        $child->setName($identifier);
        $this->children->set($identifier, $child);
        return $child;
    }

    public function getValue() : array
    {
        return $this->children->copy()->map(function ($name, $child) {
            /** @var AbstractField $child */
            return $child->getValue();
        })->toArray();
    }

    public function getChild($identifier) : AbstractField
    {
        $child = $this->children->get($identifier);
        if (!$child instanceof CollectibleInterface) {
            throw new \InvalidArgumentException("There is no child with identifier '$identifier' in this CollectionField.");
        }
        return $child;
    }

    public function children() : array
    {
        return $this->children->toArray();
    }

}