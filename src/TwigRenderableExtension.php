<?php namespace Dtkahl\FormBuilder;

use Dtkahl\FormBuilder\FieldSet;

class TwigRenderableExtension extends \Twig_Extension
{
    private $twig_environment;

    public function __construct(\Twig_Environment $twig_environment)
    {
        $this->twig_environment = $twig_environment;
    }

    public function getName()
    {
        return 'renderable-extension';
    }

    public function getFunctions()
    {
        return [new \Twig_Function("render", function (TwigRenderableInterface $object) {
            return $this->twig_environment->loadTemplate($object->getTemplate())->render($object->getRenderData());
        })];
    }
}
