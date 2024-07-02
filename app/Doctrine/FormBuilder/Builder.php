<?php declare(strict_types=1);

namespace App\Doctrine\FormBuilder;

use App\Doctrine\FormBuilder\Exceptions\BuilderException;
use App\Forms\Controls\ImagePreviewControl;
use App\Forms\Form;
use App\Models\Image;
use App\Models\Interfaces\IEntity;
use App\Services\DI;
use App\Services\Doctrine\EntityManager;
use App\Utils\Arrays;
use App\Utils\Faker;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Localization\Translator;
use ReflectionException;
use ReflectionProperty;

class Builder
{
    /**
     * @var array
     */
    private array $ignored = [];

    /**
     * @var array
     */
    private array $readonly = [];

    /**
     * @var array
     */
    private array $factories = [];

    /**
     * @var bool
     */
    private bool $readonlyAll = false;

    /**
     * @var array
     */
    private array $required = [];

    /**
     * @var array
     */
    private array $allowedEmptySelections = [];

    /**
     * @var array|callable[]
     */
    private array $renders = [];

    /**
     * @var array|array[]
     */
    private array $customComponents = [];

    /**
     * @var array|callable[]
     */
    private array $optionsCriteria = [];

    /**
     * @var array|callable[]
     */
    private array $optionsFilter = [];

    /**
     * @var array|array[]
     */
    private array $ordering = [];

    /**
     * @var bool
     */
    private bool $addSubmit = true;

    /**
     * @var array
     */
    private array $customAttributes = [];

    /**
     * @var array|FieldCondition[]
     */
    private array $conditions = [];

    /**
     * @var array|FieldRule[]
     */
    private array $rules = [];

    /**
     * @var array|string[]
     */
    private array $editors = [];

    /**
     * @param DI $di
     * @param EntityManager $entityManager
     * @param Translator $translator
     * @param AnnotationReader $reader
     */
    public function __construct(
        private readonly DI $di,
        private readonly EntityManager $entityManager,
        private readonly Translator $translator,
        private readonly AnnotationReader $reader
    ) {}

    /**
     * @param string $path
     * @return Builder
     */
    public function addIgnored(string $path): Builder
    {
        $this->ignored[$path] = true;

        return $this;
    }

    /**
     * @param string $path
     * @return Builder
     */
    public function addReadonly(string $path): Builder
    {
        $this->readonly[] = $path;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $callback
     * @return $this
     */
    public function setFactory(string $path, callable $callback): Builder
    {
        $this->factories[$path] = $callback;

        return $this;
    }

    /**
     * @param bool $readonlyAll
     * @return $this
     */
    public function setReadonlyAll(bool $readonlyAll): Builder
    {
        $this->readonlyAll = $readonlyAll;

        return $this;
    }

    /**
     * @param string $path
     * @return Builder
     */
    public function addRequired(string $path): Builder
    {
        $this->required[] = $path;

        return $this;
    }

    /**
     * @param string $path
     * @return Builder
     */
    public function allowEmptySelection(string $path): Builder
    {
        $this->allowedEmptySelections[] = $path;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $render
     * @return Builder
     */
    public function setRender(string $path, callable $render): Builder
    {
        $this->renders[$path] = $render;

        return $this;
    }

    /**
     * @param callable $factory
     * @param string|null $after
     * @return $this
     */
    public function addCustomComponent(callable $factory, string $after = null): self
    {
        $this->customComponents[$after][] = $factory;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $criteria
     * @return Builder
     */
    public function setOptionsCriteria(string $path, callable $criteria): Builder
    {
        $this->optionsCriteria[$path] = $criteria;

        return $this;
    }

    /**
     * @param string $path
     * @param callable $filter
     * @return $this
     */
    public function setOptionsFilter(string $path, callable $filter): Builder
    {
        $this->optionsFilter[$path] = $filter;

        return $this;
    }

    /**
     * @param string $path
     * @param array $ordering
     * @return $this
     */
    public function setOrdering(string $path, array $ordering): Builder
    {
        $this->ordering[$path] = $ordering;

        return $this;
    }

    /**
     * @param bool $addSubmit
     * @return Builder
     */
    public function setAddSubmit(bool $addSubmit): Builder
    {
        $this->addSubmit = $addSubmit;

        return $this;
    }

    /**
     * @param string $path
     * @param array $attributes
     * @return Builder
     */
    public function setCustomAttributes(string $path, array $attributes): Builder
    {
        if (empty($this->customAttributes[$path])) {
            $this->customAttributes[$path] = $attributes;

            return $this;
        }

        $this->customAttributes[$path] = array_merge(
            $this->optionsCriteria[$path],
            $attributes
        );

        return $this;
    }

    /**
     * @param string $path
     * @param $validator
     * @param null $value
     * @param callable|null $callback
     * @return $this
     */
    public function setCondition(string $path, $validator, $value = null, callable $callback = null): self
    {
        $this->conditions[] = new FieldCondition($path, $validator, $value, $callback);

        return $this;
    }

    /**
     * @param string $path
     * @param $validator
     * @param null $errorMessage
     * @param null $arg
     * @return $this
     */
    public function setRule(string $path, $validator, $errorMessage = null, $arg = null): self
    {
        $this->rules[] = new FieldRule($path, $validator, $errorMessage, $arg);

        return $this;
    }

    /**
     * @param string $path
     * @return Builder
     */
    public function addEditor(string $path): Builder
    {
        $this->editors[] = $path;
        $this->ignored[$path . 'Images'] = false;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getEditors(): array
    {
        return $this->editors;
    }

    /**
     * @param IEntity $entity
     * @return Form
     * @throws ReflectionException
     */
    public function create(IEntity $entity): Form
    {
        $entities = [];
        $this->prefillEntity($entity);

        $form = (new Form())->setBuilder($this);
        $form->addGroup();

        foreach ($entity->_getEntityProperties() as $property => $value) {
            $this->convert($form, $entity, $property, $property, $value);
            $this->buildCustomComponents($form, $property);
            $entities[] = $property;
        }

        $this->buildCustomComponents($form, null);
        $this->checkUndefinedProperties($entities);

        if ($this->addSubmit) {
            $form->addSubmit('submit', $this->translator->translate('actions.Save'));
        }

        if ($postSave = ($_GET['postSave'] ?? null)) {
            $form->addHidden('_postSave')->setOmitted(false)->setDisabled(false);
            $form->setDefault('_postSave', $postSave);
        }

        foreach ($this->conditions as $condition) {
            $condition->process($form);
        }

        foreach ($this->rules as $rule) {
            $rule->process($form);
        }

        return $form;
    }

    /**
     * @param IEntity $entity
     */
    private function prefillEntity(IEntity $entity): void
    {
        $prefill = (array) ($_GET['entity-pre-fill'] ?? []);
        if (empty($prefill)) {
            return;
        }

        foreach ($prefill as $property => $value) {
            $entity->_setEntityProperty($property, unserialize(base64_decode($value), ['allowed_classes' => true]));
        }
    }

    /**
     * @param array $entities
     */
    private function checkUndefinedProperties(array $entities): void
    {
        if ($diff = array_diff(array_keys(array_filter($this->ignored)), $entities)) {
            throw new BuilderException('Undefined property "' . reset($diff) . '" for ignoring. Available entities: ' . implode(', ', $entities));
        }
        if ($diff = array_diff($this->readonly, $entities)) {
            throw new BuilderException('Undefined property "' . reset($diff) . '" for read only. Available entities: ' . implode(', ', $entities));
        }
        if ($diff = array_diff($this->required, $entities)) {
            throw new BuilderException('Undefined property "' . reset($diff) . '" for required state. Available entities: ' . implode(', ', $entities));
        }
    }

    /**
     * @param string $path
     * @return bool
     */
    private function hasRender(string $path): bool
    {
        return array_key_exists($path, $this->renders);
    }

    /**
     * @param string $path
     * @return callable
     */
    private function getRender(string $path): callable
    {
        if (! array_key_exists($path, $this->renders)) {
            throw new BuilderException('Undefined render of "' . $path . '" property.');
        }

        return $this->renders[$path];
    }

    /**
     * @param string $path
     * @param Criteria $criteria
     * @return Criteria
     */
    private function getOptionsCriteria(string $path, Criteria $criteria): Criteria
    {
        if (! array_key_exists($path, $this->optionsCriteria)) {
            return $criteria;
        }

        return $this->optionsCriteria[$path]($criteria);
    }

    /**
     * @param string $path
     * @return array
     */
    private function getOrdering(string $path): array
    {
        if (empty($this->ordering[$path])) {
            return [];
        }

        return $this->ordering[$path];
    }

    /**
     * @param Container $container
     * @param IEntity $entity
     * @param string $property
     * @param string $path
     * @param $value
     * @throws ReflectionException
     */
    private function convert(Container $container, IEntity $entity, string $property, string $path, $value): void
    {
        if (isset($this->ignored[$path])) {
            return;
        }

        if (isset($this->factories[$path])) {
            $this->factories[$path]($container, $value, $entity);

            return;
        }

        $reflection = new ReflectionProperty($entity::_getEntityClassName(), $property);
        $annotations = $this->reader->getPropertyAnnotations($reflection);
        $annotation = reset($annotations);

        if (! $annotation) {
            return;
        }

        if ($annotation instanceof Column) {
            $this->convertColumn($container, $annotation, $entity, $property, $path, $value);

            return;
        }

        if ($annotation instanceof ManyToOne) {
            $this->convertManyToOne($container, $annotation, $property, $path, $value);

            return;
        }

        if ($annotation instanceof ManyToMany) {
            $this->convertManyToMany($container, $annotation, $property, $path, $value);

            return;
        }

        if ($annotation instanceof OneToOne) {
            $this->convertOneToOne($container, $annotation, $property, $path, $value);

            return;
        }

        if ($annotation instanceof Id) {
            $this->convertIdentifier($container, $property, $value);

            return;
        }

        throw new BuilderException(
            sprintf('I don\'t know what to do with annotation of "%s" type. Annotation for "%s::$%s (path: %s)". Info:',
                get_class($annotation),
                get_class($entity),
                $property,
                $path
            )
        );
    }

    /**
     * @param Container $container
     * @param string $property
     * @param $value
     */
    private function convertIdentifier(Container $container, string $property, $value): void
    {
        $input = $container->addHidden($property, $this->getFieldLabel($property));
        $input->setValue($value);
    }

    /**
     * @param Container $container
     * @param Column $annotation
     * @param string $property
     * @param string $path
     * @param $value
     * @param IEntity $entity
     */
    private function convertColumn(Container $container, Column $annotation, IEntity $entity, string $property, string $path, $value): void
    {
        $label = $this->getFieldLabel($property);
        switch ($annotation->type) {
            case 'string':
            case 'text':
                $isEditor = in_array($path, $this->editors, true);
                $input = $annotation->type === 'text' || $isEditor
                    ? $container->addTextArea($property, $label)
                    : $container->addText($property, $label);

                $input
                    ->setMaxLength($annotation->length ?? ($annotation->type === 'string' ? 255 : 65535))
                    ->setOption('id', $property);

                if ($property === 'extId') {
                    $input->setDisabled();
                }

                $this->updateControlStates($input, $path);
                $input->setValue($value ?: $this->getPrefillValue($property, $annotation));
                $this->assignCustomAttributes($input, $path);

                if ($isEditor) {
                    $input->getControlPrototype()->setAttribute('class', 'editor');
                }

                return;

            case 'boolean':
                $input = $container->addRadioList($property, $label, [
                    1 => $this->translator->translate('texts.Yes'),
                    0 => $this->translator->translate('texts.No'),
                ])->setOption('id', $property);
                $this->updateControlStates($input, $path);
                $input->setValue((int) ($value ?: $this->getPrefillValue($property, $annotation)));
                $this->assignCustomAttributes($input, $path);

                return;

            case 'integer':
                $input = $container->addText($property, $label)->setOption('id', $property);
                $input->getControlPrototype()
                    ->setAttribute('type', 'number');
                $this->updateControlStates($input, $path);
                $input->setValue((int) ($value ?: $this->getPrefillValue($property, $annotation)));
                $this->assignCustomAttributes($input, $path);

                return;

            case 'decimal':
                $precision = min($annotation->precision, 9);
                $step = 0.1 ** $annotation->scale;
                $max = 10 ** ($precision - $annotation->scale) - $step;
                $min = (-1) * $max;

                $input = $container->addText($property, $label)->setOption('id', $property);
                $input->getControlPrototype()
                    ->setAttribute('type', 'number')
                    ->setAttribute('max', $max)
                    ->setAttribute('min', $min)
                    ->setAttribute('step', $step);

                $this->updateControlStates($input, $path);
                $input->setValue((float) ($value ?: $this->getPrefillValue($property, $annotation)));
                $this->assignCustomAttributes($input, $path);

                return;

            case 'float':
                $input = $container->addText($property, $label)->setOption('id', $property);
                $input->getControlPrototype()
                    ->setAttribute('type', 'number')
                    ->setAttribute('max', 99999999999999)
                    ->setAttribute('min', -99999999999999)
                    ->setAttribute('step', 0.0001);

                $this->updateControlStates($input, $path);
                $input->setValue((float) ($value ?: $this->getPrefillValue($property, $annotation)));
                $this->assignCustomAttributes($input, $path);

                return;

            case 'datetime':
                $input = $container->addText($property, $label)->setOption('id', $property);
                $input->getControlPrototype()->setAttribute('type', 'datetime-local');
                $this->updateControlStates($input, $path);
                $input->setValue($value ? $value->format('Y-m-d\TH:i:s') : null);
                $this->assignCustomAttributes($input, $path);

                return;

            case 'date':
                $input = $container->addText($property, $label)->setOption('id', $property);
                $this->updateControlStates($input, $path);
                $input->setValue($value ? $value->format('Y-m-d') : null);
                $this->assignCustomAttributes($input, $path);

                return;

            case 'enum':
                preg_match_all('/\'(.*?)\'/s', $annotation->columnDefinition, $matches);
                $options = $matches[1];

                if ($this->hasRender($property)) {
                    $labels = array_map($this->getRender($property), $options);
                } else {
                    $labels = $options;
                }

                $input = $container->addSelect($property, $label, array_combine($options, $labels))->setOption('id', $property);
                $this->updateControlStates($input, $path);
                $input->setValue((string) $value);
                $this->assignCustomAttributes($input, $path);
                $input->getControlPrototype()->setAttribute('class', 'js-custom-select');

                return;
        }

        throw new BuilderException('Undefined column type "' . $annotation->type . '" of "' . $property . '" property.');
    }

    /**
     * @param Container $container
     * @param ManyToOne $annotation
     * @param string $property
     * @param string $path
     * @param IEntity|null $value
     */
    private function convertManyToOne(
        Container $container,
        ManyToOne $annotation,
        string $property,
        string $path,
        ?IEntity $value
    ): void {
        if ($this->convertWellKnownManyToOne($container, $annotation, $property, $value)) {
            return;
        }

        $options = $this->buildOptions(
            $path,
            $annotation->targetEntity,
            $this->getOrdering($path),
            in_array($path, $this->allowedEmptySelections, true)
        );

        $input = $container->addSelect(
            $property,
            $this->getFieldLabel($property),
            $options
        )->setOption('id', $property);
        $this->updateControlStates($input, $path);

        $input->setValue($value ? $value->getId() : $this->getPrefillValue($property));
        $this->assignCustomAttributes($input, $path);
        $input->getControlPrototype()->setAttribute('class', 'js-custom-select');
    }

    /**
     * @param Container $container
     * @param OneToOne $annotation
     * @param string $property
     * @param string $path
     * @param IEntity|null $value
     */
    private function convertOneToOne(
        Container $container,
        OneToOne $annotation,
        string $property,
        string $path,
        ?IEntity $value
    ): void {
        $options = $this->buildOptions(
            $path,
            $annotation->targetEntity,
            $this->getOrdering($path),
            in_array($path, $this->allowedEmptySelections, true)
        );

        $input = $container->addSelect(
            $property,
            $this->getFieldLabel($property),
            $options
        )->setOption('id', $property);
        $this->updateControlStates($input, $path);

        $input->setValue($value ? $value->getId() : $this->getPrefillValue($property));
        $this->assignCustomAttributes($input, $path);
        $input->getControlPrototype()->setAttribute('class', 'js-custom-select');
    }

    /**
     * @param Container $container
     * @param ManyToOne $annotation
     * @param string $property
     * @param IEntity|null $value
     * @return bool
     */
    private function convertWellKnownManyToOne(
        Container $container,
        ManyToOne $annotation,
        string $property,
        ?IEntity $value
    ): bool {
        if ($annotation->targetEntity === '\\' . Image::class) {
            $container[$property] = new ImagePreviewControl($this->di, $this->getFieldLabel($property));
            $container[$property]->setValue($value);

            return true;
        }

        return false;
    }

    /**
     * @param Container $container
     * @param ManyToMany $annotation
     * @param string $property
     * @param string $path
     * @param Collection $value
     */
    private function convertManyToMany(
        Container $container,
        ManyToMany $annotation,
        string $property,
        string $path,
        Collection $value
    ): void {
        $input = $container->addMultiSelect(
            $property,
            $this->getFieldLabel($property),
            $this->buildOptions($path, $annotation->targetEntity, $this->getOrdering($path))
        )->setOption('id', $property);
        $this->updateControlStates($input, $path);
        $input->setValue($this->exportCollectionIds($value));
        $this->assignCustomAttributes($input, $path);
        $input->getControlPrototype()->setAttribute('class', 'js-custom-select');
    }

    /**
     * @param Collection $collection
     * @return array
     */
    private function exportCollectionIds(Collection $collection): array
    {
        return $collection->map(static function (IEntity $entity) {
            return $entity->getId();
        })->toArray();
    }

    /**
     * @param string $path
     * @param string $entity
     * @param array $orderBy
     * @param bool $emptyFirst
     * @return array
     */
    private function buildOptions(string $path, string $entity, array $orderBy = [], bool $emptyFirst = false): array
    {
        $options = [];
        $criteria = $this->getOptionsCriteria($path, new Criteria());

        if (! empty($orderBy)) {
            $criteria->orderBy($orderBy);
        }

        /** @var Collection $results */
        $results = $this->entityManager
            ->getRepository($entity)
            ->matching($criteria);

        // options filter
        if (isset($this->optionsFilter[$path])) {
            $filter = $this->optionsFilter[$path];
            $results = $results->filter($filter);
        }

        /** @var IEntity $item */
        foreach ($results as $item) {
            $render = $this->getRender($path);
            $options[$item->getId()] = $render($item);
        }

        if (empty($orderBy)) {
            $options = Arrays::arSort($options);
        }

        if ($emptyFirst) {
            $options = [null => $this->translator->translate('forms.-select-')] + $options;
        }

        return $options;
    }

    /**
     * @param BaseControl $component
     * @param string $path
     */
    private function updateControlStates(BaseControl $component, string $path): void
    {
        $this->updateReadOnlyControlState($component, $path);
        $this->updateRequiredControlState($component, $path);
    }

    /**
     * @param BaseControl $component
     * @param string $path
     */
    private function updateReadOnlyControlState(BaseControl $component, string $path): void
    {
        if ($this->readonlyAll || in_array($path, $this->readonly, true)) {
            $component->setDisabled();
        }
    }

    /**
     * @param BaseControl $component
     * @param string $path
     */
    private function updateRequiredControlState(BaseControl $component, string $path): void
    {
        if (in_array($path, $this->required, true)) {
            $component->setRequired($this->translator->translate('validations.requiredField'));
        }
    }

    /**
     * @param string $name
     * @return string
     */
    private function getFieldLabel(string $name): string
    {
        return $this->translator->translate('forms.' . $name)
            . (in_array($name, $this->required, true) ? ' *' : '');
    }

    /**
     * @param BaseControl $control
     * @param string $path
     */
    private function assignCustomAttributes(BaseControl $control, string $path): void
    {
        if (empty($this->customAttributes[$path])) {
            return;
        }

        $prototype = $control->getControlPrototype();
        foreach ($this->customAttributes[$path] as $property => $value) {
            $prototype->setAttribute($property, $value);
        }
    }

    /**
     * @param string $property
     * @param Column|null $annotation
     * @return bool|float|int|mixed|string|null
     */
    private function getPrefillValue(string $property, ?Column $annotation = null): mixed
    {
        if (! empty($_GET['form-pre-fill'][$property])) {
            return $_GET['form-pre-fill'][$property];
        }

        if ($annotation && ! empty($_GET['generate'])) {
            if ($annotation->name === 'alias') {
                return null;
            }

            switch ($annotation->type) {
                case 'string':
                    return match ($annotation->name) {
                        'link', 'url', 'website' => Faker::domain(['https', 'http', 'ftp']),
                        'email' => Faker::email(),
                        'phone' => Faker::phone(['+420', '+421', '']),
                        default => trim(Faker::sentence(2, 14), '.'),
                    };

                case 'text':
                    return Faker::paragraph(3, 36);

                case 'boolean':
                    return Faker::boolean();

                case 'integer':
                    return Faker::integer();

                case 'decimal':
                case 'float':
                    return Faker::float();
            }
        }

        return null;
    }

    /**
     * @return Generator
     */
    /*
    private function getFakerGenerator(): Generator
    {
        if (! $this->generator) {
            $this->generator = Factory::create();
        }

        return $this->generator;
    }*/

    /**
     * @param Form $form
     * @param string|null $path
     * @return void
     */
    private function buildCustomComponents(Form $form, ?string $path): void
    {
        if (isset($this->customComponents[$path])) {
            foreach ($this->customComponents[$path] as $factory) {
                $factory($form);
            }
        }

    }
}
