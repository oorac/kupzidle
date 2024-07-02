<?php declare(strict_types=1);

namespace App\Doctrine\FormBuilder;

use App\Forms\Form;
use Closure;
use RuntimeException;

class FieldRule
{
    /**
     * @param string $property
     * @param mixed $validator
     * @param mixed|null $errorMessage
     * @param Closure|null $arg
     */
    public function __construct(
        private readonly string $property,
        private readonly mixed $validator,
        private readonly mixed $errorMessage = null,
        private readonly ?Closure $arg = null
    ) {}

    /**
     * @param Form $form
     */
    public function process(Form $form): void
    {
        if (empty($form[$this->property])) {
            throw new RuntimeException(sprintf('Undefined form field "%s"', $this->property));
        }

        $form[$this->property]->addRule($this->validator, $this->errorMessage, $this->arg);
    }
}
