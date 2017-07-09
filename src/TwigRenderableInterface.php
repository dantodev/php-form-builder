<?php namespace Dtkahl\FormBuilder;

/**
 * A class which implement this interface can be passed into render function from TwigRenderableExtension
 */
interface TwigRenderableInterface {

    /**
     * Return the path to template file.
     *
     * @return string
     */
    public function getTemplate() : string;

    /**
     * Return the render date for the template.
     *
     * @param array $data
     * @return array
     */
    public function getRenderData(array $data) : array;

}