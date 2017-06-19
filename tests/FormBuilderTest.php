<?php namespace Dtkahl\FormBuilderTest;

use Dtkahl\FormBuilder\Field;
use Dtkahl\FormBuilder\FieldSet;
use Respect\Validation\Validator;

// TODO upgrade to newest PHPUnit for PHP7 only because we can ;)
class FormBuilderTest extends \PHPUnit_Framework_TestCase
{

    /** @var FieldSet */
    public $form;

    public function setUp()
    {
        $sub_form = new class extends FieldSet {
            public function setUp() {
                $this->setField('first_name', new Field);
                $this->setField('last_name', new Field);
            }
            public function setUpValidators() {
                $this->setValidator('first_name', Validator::stringType());
                $this->setValidator('last_name', Validator::stringType());
            }
            public function save() {}
        };
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
            public function save() {}
        };
    }

    public function testValidationMessages()
    {
        $this->assertFalse($this->form->isValid());
        $this->assertEquals(["email", "name"], array_keys($this->form->getMessages()));
        $this->form->getField('email')->setValue(12);
        $this->form->getFieldSet('name')->getField('first_name')->setValue('John');
        $this->form->getFieldSet('name')->getField('last_name')->setValue('Smith');
        $this->assertFalse($this->form->isValid());
        $this->assertTrue($this->form->hasMessages());
        $this->assertTrue($this->form->hasMessages('email'));
        $this->assertEquals(["email"], array_keys($this->form->getMessages()));
        $this->form->getField('email')->setValue('john.smith@tardis.space');
        $this->assertTrue($this->form->isValid());
        $this->assertFalse($this->form->hasMessages());
        $this->assertFalse($this->form->hasMessages('email'));
        $this->assertEmpty(array_keys($this->form->getMessages()));
    }

    public function testHydrate()
    {
        $this->form->hydrate([
            'name' => ['first_name' => 'John', 'last_name' => 'Smith'],
            'email' => 'john.smith@tardis.space',
            'age' => 42
        ]);
        $this->assertEquals('sjohn.smith@tardis.space', $this->form->getValue('email'));
        $this->assertEquals(42, $this->form->getValue('age'));
        $this->assertEquals('John', $this->form->getFieldSet('name')->getValue('first_name'));
        $this->assertTrue($this->form->isValid());
    }

}