<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\MapField;
use Respect\Validation\Validator;

class TestSubForm extends MapField
{

    public function setUp() : void {
        $this->setChild('first_name')->setValidator(Validator::stringType());
        $this->setChild('last_name')->setValidator(Validator::stringType());
    }

}