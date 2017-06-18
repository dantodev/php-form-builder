<?php namespace Dtkahl\FormBuilder;

abstract class FormElement
{

    /**
     * @param $data
     * @return mixed|void
     */
    abstract public function hydrate($data);

    /**
     * @return mixed|void
     */
    abstract public function render();

    /**
     * @return mixed|void
     */
    abstract public function save();

}