<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\Field;
use Dtkahl\FormBuilder\MapField;
use Respect\Validation\Validator;

class TestForm extends MapField
{

    public function setUp() : void {
        $this->setChild('name', new TestSubForm);
        $this->setChild('email')->setValidator(Validator::email());
        $this->setChild('age', new Field(['options_heredity' => false]));
    }

}