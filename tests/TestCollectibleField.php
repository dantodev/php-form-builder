<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\CollectibleInterface;
use Dtkahl\FormBuilder\MapField;
use Respect\Validation\Validator;

class TestCollectibleField extends MapField implements CollectibleInterface
{

    public function setUp() : void {
        $this->setChild('id');
        $this->setChild('foo')->setValidator(Validator::alpha());
    }

    public function getUniqueIdentifier()
    {
        return $this->getChild('id')->getValue();
    }
}