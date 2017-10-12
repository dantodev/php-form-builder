<?php namespace Dtkahl\FormBuilder;

interface MapperInterface
{

    /**
     * Given value is the result of "$field->fromValue()".
     * The returned value of this method will be returned by "$field->getValue()".
     *
     * @param $value
     * @return mixed
     */
    public function map($value);

    /**
     * Given value is the value which has been passed to "$field->setValue()".
     * The returned value of this method will be passed to "$field->fromValue()".
     *
     * @param $value
     * @return mixed
     */
    public function unmap($value);

}