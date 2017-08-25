<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\CollectibleInterface;
use Dtkahl\FormBuilder\MapField;
use Dtkahl\FormBuilder\RespectValidator;
use Respect\Validation\Validator;

class TestCollectibleField extends MapField implements CollectibleInterface
{

    public function setUp() : void {
        $this->setChild('id');
        $this->setChild('foo')->setValidator(new RespectValidator(Validator::alpha()));
    }

    public function getUniqueIdentifier()
    {
        return $this->getChild('id')->getValue();
    }
}