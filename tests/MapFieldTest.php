<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\MapperInterface;
use Dtkahl\FormBuilder\RespectValidator;
use PHPUnit\Framework\TestCase;
use Dtkahl\FormBuilder\MapField;
use Respect\Validation\Validator;

class MapFieldTest extends TestCase
{

    /** @var MapField */
    public $form;

    public function setUp()
    {
        $this->form = new class extends MapField {
            protected $options = ["default_option" => 'is set'];
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
        $this->assertArrayHasKey("map", $this->form->toSerializedArray());
    }

    public function testPredefinedDefaultOptions()
    {
        $this->assertEquals("is set", $this->form->getOption("default_option"));
    }

    public function testMapper()
    {
        $data = ["name" => "John", "age" => 22];
        $this->form->setMapper(new class implements MapperInterface {
            public function map($value)
            {

                return ["mapped" => [
                    "name" => $value["name"],
                    "age" => $value["age"],
                ]];
            }
            public function unmap($value)
            {
                return $value["mapped"];
            }
        });
        $this->form->setValue(["mapped" => $data]);
        $this->assertEquals(["mapped" => $data], $this->form->getValue());
        $this->assertEquals($data, $this->form->getUnmappedValue());
        $this->form->unsetMapper();
        $this->assertEquals($data, $this->form->getValue());
    }

}