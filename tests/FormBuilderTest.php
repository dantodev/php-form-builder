<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\AbstractField;
use Dtkahl\FormBuilder\CollectionField;
use PHPUnit\Framework\TestCase;
use Dtkahl\FormBuilder\Field;
use Dtkahl\FormBuilder\MapField;
use Respect\Validation\Validator;

class FormBuilderTest extends TestCase
{

    /** @var MapField */
    public $form;

    public function setUp()
    {
        $this->form = new TestForm(['test_option' => 'foo']);
    }

    public function testValidationAndMessages()
    {
        $this->assertTrue($this->form->isValid());
        $this->form->validate();
        $this->assertFalse($this->form->isValid());
        $this->assertEquals(["name", "email"], array_keys($this->form->getMessages()));
        $this->form->getChild('email')->setValue(12);
        $this->assertEquals('email', $this->form->getChild('email')->getName());
        $this->assertEquals('email', $this->form->getChild('email')->getName(true));
        $this->assertFalse($this->form->getChild('name')->getChild('first_name')->isValid());
        $this->assertFalse($this->form->getChild('name')->getChild('last_name')->isValid());
        $this->assertEquals('first_name', $this->form->getChild('name')->getChild('first_name')->getName());
        $this->assertEquals('name[first_name]', $this->form->getChild('name')->getChild('first_name')->getName(true));
        $this->form->setValue(["name" => ["first_name" => "John"]]);
        $this->form->getChild('name')->getChild('last_name')->setValue('Smith');
        $this->assertFalse($this->form->validate());
        $this->assertTrue($this->form->getChild('name')->getChild('first_name')->isValid());
        $this->assertTrue($this->form->getChild('name')->getChild('last_name')->isValid());
        $this->assertEquals(["email"], array_keys($this->form->getMessages()));
        $this->form->getChild('email')->setValue('john.smith@tardis.space');
        $this->assertTrue($this->form->validate());
        $this->assertEmpty(array_keys($this->form->getMessages()));
    }

    public function testSetValue()
    {
        $values = [
            'name' => ['first_name' => 'John', 'last_name' => 'Smith'],
            'email' => 'john.smith@tardis.space',
            'age' => 42
        ];
        $this->form->setValue($values);
        $this->assertEquals('john.smith@tardis.space', $this->form->getChild('email')->getValue());
        $this->assertEquals(42, $this->form->getChild('age')->getValue());
        $this->assertEquals('John', $this->form->getChild('name')->getChild('first_name')->getValue());
        $this->assertEquals($values, $this->form->getValue());
    }

    public function testArrayAccess()
    {
        $this->assertTrue(isset($this->form['email']));
        $this->assertTrue(isset($this->form['name']));
        $this->assertInstanceOf(Field::class, $this->form['email']);
        $this->assertInstanceOf(MapField::class, $this->form['name']);
        $this->form['foo'] = new Field;
        $this->assertInstanceOf(Field::class, $this->form['foo']);
    }

    public function testClosureValidator()
    {
        $form = new class extends MapField {
            public function setUp() : void {
                $this->setChild('foo');
                $this->setChild('bar')->setValidator(function (AbstractField $field) {
                    /** @var MapField $parent */
                    $parent = $field->getParent();
                    if ($parent->getChild('foo')->getValue() == 'yes') {
                        return Validator::notEmpty();
                    }
                    return null;
                });
            }
        };
        $this->assertTrue($form->getChild('bar')->validate());
        $form->getChild('foo')->setValue('yes');
        $this->assertFalse($form->getChild('bar')->validate());
    }

    function testOptionsHeredity()
    {
        $this->assertEquals('foo', $this->form->options()->get('test_option'));
        $this->assertEquals('foo', $this->form->getChild('email')->options()->get('test_option'));
        $this->assertFalse($this->form->getChild('age')->options()->has('test_option'));
    }

    public function testCollectionField()
    {
        $form = new CollectionField(['child_class' => TestCollectibleField::class]);
        $data = [
            ["id" => 1, "foo" => "bar"],
            ["id" => 2, "foo" => "123"],
        ];
        $this->assertEmpty($form->getValue());
        $form->setValue($data);
        $this->assertEquals(["1" => $data[0],"2" => $data[1]], $form->getValue());
        $this->assertFalse($form->validate());
        /** @var MapField $element_2 */
        $element_2 = $form->getChild(2);
        $element_2->getChild("foo")->setValue("abc");
        $this->assertTrue($form->validate());
    }

}