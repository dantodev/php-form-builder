<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\CollectionField;
use PHPUnit\Framework\TestCase;
use Dtkahl\FormBuilder\Field;
use Dtkahl\FormBuilder\MapField;

class FormBuilderTest extends TestCase
{

    /** @var CollectionField */
    private $form;

    public function setUp()
    {
        $this->form = new CollectionField(['child_class' => TestCollectibleField::class]);
    }

    public function testCollectionField()
    {

        $data = [
            ["id" => 1, "foo" => "bar"],
            ["id" => 2, "foo" => "123"],
        ];
        $this->assertEmpty($this->form->getValue());
        $this->form->setValue($data);
        $this->assertEquals(["1" => $data[0],"2" => $data[1]], $this->form->getValue());
        $this->assertFalse($this->form->validate());
        /** @var MapField $element_2 */
        $element_2 = $this->form->getChild(2);
        $element_2->getChild("foo")->setValue("abc");
        $this->assertTrue($this->form->validate());
    }

    public function testSerialize()
    {
        $this->assertEquals(array_values($this->form->toSerializedArray()), $this->form->toSerializedArray());
    }

}