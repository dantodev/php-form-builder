<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\Form;

class TestForm extends Form
{

    public function setUp()
    {
        $this->addField('name', new TestElement());
        $this->addField('email', new TestElement());
        $this->addField('age', new TestElement());
    }

}