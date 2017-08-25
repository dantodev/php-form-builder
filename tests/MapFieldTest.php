<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\CollectionField;
use Dtkahl\FormBuilder\RespectValidator;
use PHPUnit\Framework\TestCase;
use Dtkahl\FormBuilder\Field;
use Dtkahl\FormBuilder\MapField;
use Respect\Validation\Validator;

class MapFieldTest extends TestCase
{

    /** @var MapField */
    public $form;

    public function setUp()
    {
        $this->form = new class extends MapField {
            public function setUp(): void
            {
                $this->setChild("name")->setValidator(new RespectValidator(Validator::notEmpty()));
                $this->setChild("age")->setValidator(new RespectValidator(Validator::notEmpty()));
            }
        };
    }

    public function testChildren()
    {
        $this->assertCount(2, $this->form->children());
        $this->assertEquals("name", $this->form->getChild("name")->getName());
    }

    public function testValue()
    {
        $data = ["name" => "John", "age" => 22];
        $this->assertNull($this->form->getChild("name")->getValue());
        $this->assertNull($this->form->getChild("age")->getValue());
        $this->form->setValue($data);
        $this->assertEquals("John", $this->form->getChild("name")->getValue());
        $this->assertEquals(22, $this->form->getChild("age")->getValue());
        $this->assertEquals($data, $this->form->getValue());
    }

    public function testValidateAndMessages()
    {
        $data = ["name" => "John", "age" => 22];
        $this->assertFalse($this->form->validate());
        $this->assertEquals(array_keys($data), array_keys($this->form->getMessages()));
        $this->form->setValue($data);
        $this->assertTrue($this->form->validate());
        $this->assertEmpty($this->form->getMessages());
    }

    public function testConditions()
    {
        $name = $this->form->getChild("name");
        $age = $this->form->getChild("age");
        $name->setValue("John");
        $age->setOption("conditions", [["name", "==", "John"]]);
        $this->assertTrue($name->validate());
        $this->assertTrue($this->form->checkChildConditions("age"));
        $this->assertFalse($age->validate());
        $this->assertFalse($this->form->validate());
        $name->setValue("Will");
        $this->assertFalse($this->form->checkChildConditions("age"));
        $this->assertTrue($this->form->validate());
    }

    public function testSerialize()
    {
        $this->assertEquals(array_keys($this->form->children()), array_keys($this->form->toSerializedArray()));
    }

}