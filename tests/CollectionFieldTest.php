<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\CollectionField;
use PHPUnit\Framework\TestCase;
use Dtkahl\FormBuilder\MapField;

class CollectionFieldTest extends TestCase
{

    /** @var CollectionField */
    private $form;

    public function setUp()
    {
        $this->form = new CollectionField([
            'child_class' => TestCollectibleField::class,
            'child_args' => [['foo' => 'bar']]
        ]);
    }

    public function testCollectionField()
    {

        $data = [
            ["id" => 1, "foo" => "bar"],
            ["id" => 2, "foo" => "123"],
        ];
        $this->assertEmpty($this->form->getValue());
        $this->form->setValue($data);
        $this->assertEquals($data, $this->form->getValue());
        $this->assertFalse($this->form->validate());
        /** @var MapField $element_2 */
        $element_2 = $this->form->getChild(2);
        $element_2->getChild("foo")->setValue("abc");
        $this->assertTrue($this->form->validate());
        $this->assertEquals('bar', $element_2->getOption("foo"));
    }

    public function testSerialize()
    {
        $this->assertArrayHasKey("collection", $this->form->toSerializedArray());
        $this->assertArrayHasKey("new_config", $this->form->toSerializedArray());
    }

}