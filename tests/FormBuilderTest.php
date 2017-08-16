<?php namespace Dtkahl\FormBuilderTest;

use PHPUnit\Framework\TestCase;
use Dtkahl\FormBuilder\Field;
use Dtkahl\FormBuilder\FieldSet;
use Respect\Validation\Validator;

class FormBuilderTest extends TestCase
{

    /** @var FieldSet */
    public $form;
    public $sub_form;

    public function setUp()
    {
        $sub_form = new class extends FieldSet {
            public function setUp() {
                $this->set('first_name', new Field);
                $this->set('last_name');
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
                $this->set('name', $this->sub_form);
                $this->set('email', new Field);
                $this->set('age', new Field);
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
        $this->assertEquals(["name", "email"], array_keys($this->form->getMessages()));
        $this->form->get('email')->setValue(12);
        $this->assertEquals('email', $this->form->get('email')->getName());
        $this->assertEquals('email', $this->form->get('email')->getName(true));
        $this->assertFalse($this->form->get('name')->get('first_name')->isValid());
        $this->assertFalse($this->form->get('name')->get('last_name')->isValid());
        $this->assertEquals('first_name', $this->form->get('name')->get('first_name')->getName());
        $this->assertEquals('name[first_name]', $this->form->get('name')->get('first_name')->getName(true));
        $this->form->hydrate(["name" => ["first_name" => "John"]]);
        $this->form->get('name')->get('last_name')->setValue('Smith');
        $this->assertFalse($this->form->validate());
        $this->assertTrue($this->form->get('name')->get('first_name')->isValid());
        $this->assertTrue($this->form->get('name')->get('last_name')->isValid());
        $this->assertEquals(["email"], array_keys($this->form->getMessages()));
        $this->form->get('email')->setValue('john.smith@tardis.space');
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
        $this->assertEquals('John', $this->form->get('name')->getValue('first_name'));
        $this->assertEquals($values, $this->form->getValue());
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