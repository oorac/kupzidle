<?php declare(strict_types=1);

namespace App\Doctrine\FormBuilder;

use App\Forms\Form;
use Closure;
use RuntimeException;

class FieldCondition
{
    /**
     * @param string $property
     * @param mixed $validator
     * @param mixed|null $value
     * @param Closure|null $callback
     */
    public function __construct(
        private readonly string $property,
        private readonly mixed $validator,
        private readonly mixed $value = null,
        private readonly ?Closure $callback = null
    ) {}

    /**
     * @param Form $form
     */
    public function process(Form $form): void
    {
        if (empty($form[$this->property])) {
            throw new RuntimeException(sprintf('Undefined form field "%s"', $this->property));
        }

        $rules = $form[$this->property]->addCondition($this->validator, $this->value);
        if (is_callable($this->callback)) {
            call_user_func($this->callback, $rules);
        }
    }
}
