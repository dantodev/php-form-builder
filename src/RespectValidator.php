<?php namespace Dtkahl\FormBuilder;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

class RespectValidator
{
    private $validator;

    /**
     * RespectValidator constructor.
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param callable $builder
     * @return RespectValidator
     */
    public static function build(callable $builder)
    {
        return new self($builder(new Validator));
    }

    /**
     * @param AbstractField $field
     * @return array
     */
    public function __invoke(AbstractField $field): array
    {
        $this->validator->setName($field->getOption('label', $field->getName()));
        try {
            $this->validator->assert($field->getValue());
        } catch (NestedValidationException $e) {
            $validation_params = $field->getOption('validation_params', []);
            $e->setParams($validation_params);
            foreach ($e->getIterator() as $e2) {
                $e2->setParams($validation_params);
            }
            return $e->getMessages();
        }
        return [];
    }

}