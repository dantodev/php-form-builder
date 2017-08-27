<?php namespace Dtkahl\FormBuilderTest;

use PHPUnit\Framework\TestCase;
use Dtkahl\FormBuilder\Field;

class FieldTest extends TestCase
{

    public function testValue()
    {
        $field = new Field;
        $this->assertNull($field->getValue());
        $field->setValue("test");
        $this->assertEquals("test", $field->getValue());

    }

    public function testName()
    {
        $field = new Field;
        $field->setName("name");
        $this->assertEquals("name", $field->getName());

    }

    public function testOptions()
    {
        $field = new Field;
        $field->setOption("foo", "bar");
        $this->assertEquals("bar", $field->getOption("foo"));
        $this->assertEquals("default", $field->getOption("unset", "default"));

    }

    public function testValidatorAndMessage()
    {
        $field = new Field;
        $field->setValue("bad");
        $this->assertTrue($field->validate());
        $field->setValidator(function (Field $field) {
            $messages = [];
            if ($field->getValue() != "foo") {
                $messages[] = "Error";
            }
            return $messages;
        });
        $this->assertFalse($field->validate());
        $this->assertEquals(["Error"], $field->getMessages());
        $field->setValue("foo");
        $this->assertTrue($field->validate());
        $field->setValue("bad again");
        $this->assertFalse($field->validate());
        $field->removeValidator();
        $this->assertTrue($field->validate());
    }

    public function testFormatter()
    {
        $field = new Field;
        $field->setValue("true");
        $this->assertEquals("true", $field->getValue());
        $field->setFormatter(function ($value) {
            return boolval($value);
        });
        $this->assertEquals(true, $field->getValue());
        $field->removeFormatter();
        $this->assertEquals("true", $field->getValue());
    }

    public function testSerialize()
    {
        $field = new Field;
        $field->setName("foo");
        $field->setValue("bar");
        $field->setOption("key", "value");
        $this->assertEquals(["name" => "foo", "options" => ["key" => "value"], "messages" => [], "valid" => true, "value" => "bar"], $field->toSerializedArray());
    }

}