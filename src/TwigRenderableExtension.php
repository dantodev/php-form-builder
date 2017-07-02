<?php namespace Dtkahl\FormBuilder;

use Dtkahl\FormBuilder\FieldSet;
use Twig_Token;

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

    public function getTokenParsers()
    {
        return [new class extends \Twig_TokenParser {
            public function getTag() {
                return 'render';
            }
            public function parse(Twig_Token $token) {
                $stream = $this->parser->getStream();
                $expr = $this->parser->getExpressionParser()->parseExpression();
                $stream->expect(Twig_Token::BLOCK_END_TYPE);

                return new class($expr, $token->getLine(), $this->getTag()) extends \Twig_Node {
                    public function __construct($expr, $lineno = 0, $tag = null) {
                        parent::__construct(["expr" => $expr], [], $lineno, $tag);
                    }
                    public function compile(\Twig_Compiler $compiler) {
                        $compiler
                            ->addDebugInfo($this)
                            ->raw("(function (\$renderable) {\n")
                            ->raw("if (!\$renderable instanceof \Dtkahl\FormBuilder\TwigRenderableInterface) {\n")
                            ->raw("throw new \InvalidArgumentException(\"Must implement RenderableInterface.\");\n")
                            ->raw("}\n")
                            ->raw("\$this")
                            ->raw("->loadTemplate(\$renderable->getTemplate())")
                            ->raw("->display(\$renderable->getRenderData());\n")
                            ->raw("})(")
                            ->subcompile($this->getNode('expr'))
                            ->raw(");\n")
                        ;
                    }
                };
            }
        }];
    }
}
