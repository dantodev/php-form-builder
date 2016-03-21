<?php namespace Dtkahl\FormBuilder;

use Dtkahl\FormBuilder\Interfaces\FormElementInterface;
use Dtkahl\FormBuilder\Interfaces\FormInterface;
use Dtkahl\FormBuilder\Traits\FormElementTrait;
use Dtkahl\FormBuilder\Traits\FormTrait;

class FormBuilderTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @var FormElementInterface
   */
  public $form;

  public function setUp()
  {
    $this->form = (new FormBuilder())->registerForm('test_form', TestForm::class);
    $this->form->registerElement('test_element1', TestElement::class);
    $this->form->registerElement('test_element2', TestElement::class);
  }

  public function testRender()
  {
    $this->form->render();
    $this->assertEquals(
        ['test_element1_render' => true, 'test_element2_render' => true],
        $this->form->properties->all()
    );
  }

  public function testSave()
  {
    $this->form->save();
    $this->assertEquals(
        ['test_element1_save' => true, 'test_element2_save' => true],
        $this->form->properties->all()
    );
  }

}

class TestForm implements FormInterface
{
  use FormTrait;

  public function render()
  {
    foreach ($this->_elements as $element) {
      /** @var FormElementInterface $element */
      $element->render();
    }
  }

  public function save()
  {
    foreach ($this->_elements as $element) {
      /** @var FormElementInterface $element */
      $element->save();
    }
  }

}

class TestElement implements FormElementInterface
{
  use FormElementTrait;

  public function render()
  {
    $this->_form->properties->set($this->getName().'_render', true);
  }

  public function save()
  {
    $this->_form->properties->set($this->getName().'_save', true);
  }

}