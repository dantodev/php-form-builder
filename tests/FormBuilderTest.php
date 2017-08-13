<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\Field;
use Dtkahl\FormBuilder\FieldSet;
use Respect\Validation\Validator;

// TODO upgrade to newest PHPUnit for PHP7 only because we can ;)
class FormBuilderTest extends \PHPUnit_Framework_TestCase
{

    /** @var FieldSet */
    public $form;
    public $sub_form;

    public function setUp()
    {
        $sub_form = new class extends FieldSet {
            public function setUp() {
                $this->setField('first_name', new Field);
                $this->setField('last_name');
            }
            public function setUpValidators() {
                $this->setValidator('first_name', Validator::stringType());
                $this->setValidator('last_name', Validator::stringType());
            }
        };
        $this->sub_form = $sub_form;
        $this->form = new class($sub_form) extends FieldSet {
            private $sub_form;
            public function __construct($sub_form) {
                $this->sub_form = $sub_form;
                parent::__construct();
            }
            public function setUp() {
                $this->setFieldSet('name', $this->sub_form);
                $this->setField('email', new Field);
                $this->setField('age', new Field);
            }
            public function setUpValidators() {
                $this->setValidator('email', Validator::email());
            }
        };
    }

    public function testValidationAndMessages()
    {
        $this->assertTrue($this->form->isValid());
        $this->assertFalse($this->form->validate());
        $this->assertFalse($this->form->isValid());
        $this->assertEquals(["email", "name"], array_keys($this->form->getMessages()));
        $this->form->getField('email')->setValue(12);
        $this->assertEquals('email', $this->form->getField('email')->getName());
        $this->assertEquals('email', $this->form->getField('email')->getName(true));
        $this->assertFalse($this->form->getFieldSet('name')->getField('first_name')->isValid());
        $this->assertFalse($this->form->getFieldSet('name')->getField('last_name')->isValid());
        $this->assertEquals('first_name', $this->form->getFieldSet('name')->getField('first_name')->getName());
        $this->assertEquals('name[first_name]', $this->form->getFieldSet('name')->getField('first_name')->getName(true));
        $this->form->hydrate(["name" => ["first_name" => "John"]]);
        $this->form->getFieldSet('name')->getField('last_name')->setValue('Smith');
        $this->assertFalse($this->form->validate());
        $this->assertTrue($this->form->getFieldSet('name')->getField('first_name')->isValid());
        $this->assertTrue($this->form->getFieldSet('name')->getField('last_name')->isValid());
        $this->assertEquals(["email"], array_keys($this->form->getMessages()));
        $this->form->getField('email')->setValue('john.smith@tardis.space');
        $this->assertTrue($this->form->validate());
        $this->assertEmpty(array_keys($this->form->getMessages()));
    }

    public function testHydrate()
    {
        $values = [
            'name' => ['first_name' => 'John', 'last_name' => 'Smith'],
            'email' => 'john.smith@tardis.space',
            'age' => 42
        ];
        $this->form->hydrate($values);
        $this->assertEquals('john.smith@tardis.space', $this->form->getValue('email'));
        $this->assertEquals(42, $this->form->getValue('age'));
        $this->assertEquals('John', $this->form->getFieldSet('name')->getValue('first_name'));
        $this->assertEquals($values, $this->form->getValues());
    }

    public function testArrayAccess()
    {
        $this->assertNull($this->form['foo']);
        $this->assertFalse(isset($this->form['foo']));
        $this->assertTrue(isset($this->form['email']));
        $this->assertTrue(isset($this->form['name']));
        $this->assertInstanceOf(Field::class, $this->form['email']);
        $this->assertInstanceOf(FieldSet::class, $this->form['name']);
        unset($this->form['email']);
        $this->assertNull($this->form['email']);
        unset($this->form['name']);
        $this->assertNull($this->form['name']);
        $this->form['email'] = new Field;
        $this->assertInstanceOf(Field::class, $this->form['email']);
        $this->form['name'] = $this->sub_form;
        $this->assertInstanceOf(FieldSet::class, $this->form['name']);
    }

}