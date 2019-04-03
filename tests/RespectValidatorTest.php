<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\RespectValidator;
use PHPUnit\Framework\TestCase;
use Dtkahl\FormBuilder\Field;
use Respect\Validation\Validator;

class RespectValidatorTest extends TestCase
{

    public function testWithClosure()
    {
        $field = new Field;
        $field->setValidator(RespectValidator::build(function (Validator $validator) {
            $validator->email();
            return $validator;
        }));
        $field->setValue("bad");
        $this->assertFalse($field->validate());
        $field->setValue("good@example.com");
        $this->assertTrue($field->validate());
    }

    public function testWithInstance()
    {
        $field = new Field;
        $field->setValidator(new RespectValidator(Validator::email()));
        $field->setValue("bad");
        $this->assertFalse($field->validate());
        $field->setValue("good@example.com");
        $this->assertTrue($field->validate());
    }

}