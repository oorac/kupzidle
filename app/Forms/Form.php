<?php declare(strict_types=1);

namespace App\Forms;

use App\Doctrine\FormBuilder\Builder;
use App\Forms\Controls\HoneyPot;
use App\Forms\Controls\Multiplier;
use Closure;
use Nette\Application\UI\Form as NetteForm;
use Nette\ComponentModel\IContainer;

class Form extends NetteForm
{
    /**
     * @var Builder|null
     */
    private ?Builder $builder = null;

    /**
     * @param IContainer|null $parent
     * @param string|null $name
     */
    public function __construct(IContainer $parent = null, string $name = null)
    {
        parent::__construct($parent, $name);

        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = 'div class="c-form__controls"';
        $renderer->wrappers['pair']['container'] = 'div class="c-form__pair"';
        $renderer->wrappers['label']['container'] = 'div class="c-form__label"';
        $renderer->wrappers['control']['container'] = 'div class="c-form__control"';
    }

    /**
     * @param string $name
     * @param Closure $builder
     * @param string|null $label
     * @return Multiplier
     */
	public function addMultiplier(string $name, Closure $builder, ?string $label = null): Multiplier
	{
		return $this['______' . $name] = new Multiplier($name, $builder, $label);
	}

    /**
     * @param string $name
     * @return HoneyPot
     */
    public function addHoneyPot(string $name): HoneyPot
    {
        $component = new HoneyPot();
        $this->addComponent($component, $name);

        return $component;
    }

    /**
     * @param Builder $builder
     * @return $this
     */
    public function setBuilder(Builder $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * @return Builder|null
     */
    public function getBuilder(): ?Builder
    {
        return $this->builder;
    }

    /**
     * @param string $property
     * @param $value
     * @return $this
     */
    public function setDefault(string $property, $value): self
    {
        $this->setDefaults([$property => $value]);

        return $this;
    }

    /**
     * @param array $reserved
     * @return $this
     */
    public function autoProtect(array $reserved = []): self
    {
        foreach ($this->resolveHoneyPots($reserved) as $honeyPot) {
            $this->addHoneyPot($honeyPot);
        }

        return $this;
    }

    /**
     * @return HoneyPot[]
     */
    public function getHoneyPots(): array
    {
        return iterator_to_array(
            $this->getComponents(false, HoneyPot::class)
        );
    }

    /**
     * @param array $reserved
     * @return array
     */
    private function resolveHoneyPots(array $reserved): array
    {
        $components = array_keys(iterator_to_array($this->getComponents()));

        return array_diff([
            'date',
            'key',
            'message',
            'order',
            'subject',
            'priority',
            'workspace',
            'project',
            'task',
            'greetings',
            'street',
            'address',
            'country',
        ], $reserved, $components);
    }
}
