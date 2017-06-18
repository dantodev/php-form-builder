<?php namespace Dtkahl\FormBuilderTest;

class FormBuilderTest extends \PHPUnit_Framework_TestCase
{

    /** @var TestForm */
    public $form;

    public function setUp()
    {
        $this->form = new TestForm;
    }

    // TODO test hydrate
    // TODO test validation
    // TODO test messages

    public function testRender()
    {
        $this->form->render();
        // TODO
    }

    public function testSave()
    {
        $this->form->save();
        // TODO
    }

}