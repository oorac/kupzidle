<?php declare(strict_types=1);

namespace App\Forms\Controls;

use App\Forms\Form;
use App\Utils\Random;
use Closure;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class Multiplier extends BaseControl
{
    /**
     * @var Container
     */
    private Container $container;

    /**
     * @var string|null
     */
    private ?string $uid = null;

    /**
     * @param string|null $name
     * @param Closure $builder
     * @param string|null $label
     */
    public function __construct(
        private readonly ?string $name,
        private readonly Closure $builder,
        ?string $label = null,
    ) {
        parent::__construct($label);

        $this->monitor(Form::class, function (Form $form): void {
            $this->container = $form->addContainer($this->name);

            $form->onAnchor[] = function (Form $form) {
                if (! $form->isSubmitted()) {
                    $name = round(microtime(true) * 1000) . '_' . Random::int(0, 10000);
                    $this->addContainer($name);
                }
            };
        });
    }

    /**
     * @return Html
     */
    public function getControl(): Html
    {
        $uid = $this->getUid();
        $renderer = $this->form->getRenderer();

        $container = $this->addContainer('______ITERATOR______');
        $control = $renderer->renderControls($container);
        $this->container->removeComponent($container);

        $output = Html::el('div')
            ->setAttribute('class', 'c-form__multiplier c-form__multiplier--' . $this->getHtmlName())
            ->setAttribute('data-multiplier-uid', $uid);

        foreach ($this->container->getComponents() as $container) {
            $output->addHtml(
                Html::el('div')
                    ->setAttribute('class', 'c-form__multiplier-item')
                    ->setAttribute('data-multiplier-uid', $uid)
                    ->addHtml(
                        $renderer->renderControls($container)
                    )
            );
        }

        ob_start();
        ?>
            <script>
                function <?php echo $uid; ?>_add(node) {
                    let container = node.closest('.c-form__multiplier');

                    let name = Date.now() + '_' + Math.floor(Math.random() * 10000);

                    let html = '<?php echo addslashes(str_replace(PHP_EOL, ' ', $control)); ?>';
                    html = html.replace(/______ITERATOR______/g, name);

                    let newNode = document.createElement('div');
                    newNode.classList.add('c-form__multiplier-item')
                    newNode.innerHTML = html;

                    container.appendChild(newNode);
                }

                function <?php echo $uid; ?>_remove(node) {
                    let item = node.closest('.c-form__multiplier-item');
                    let container = item.closest('.c-form__multiplier');

                    item.remove();

                    let items = container.querySelectorAll('.c-form__multiplier-item');
                    if (! items.length) {
                        <?php echo $uid; ?>_add(container);
                    }
                }
            </script>
        <?php
        $scripts = ob_get_clean();
        $output->addHtml($scripts);

        return $output;
    }

    /**
     * @return void
     */
    public function loadHttpData(): void
    {
        if (! $form = $this->getForm()) {
            return;
        }

        if (! $data = $form->getHttpData()) {
            return;
        }

        foreach ($data[$this->name] ?? [] as $key => $values) {
            $container = $this->addContainer($key);
            $container->setValues($values, true);
        }
    }

    /**
     * @param int|string $name
     * @return Container
     */
    private function addContainer(int|string $name): Container
    {
        $container = $this->container->addContainer($name);
        call_user_func($this->builder, $container, $this->form);

        $uid = $this->getUid();

        $container->addButton('_add', '+')
            ->setHtmlAttribute('class', 'c-form__multiplier-add-button')
            ->setHtmlAttribute('onclick', 'return ' . $uid . '_add(this);');

        $container->addButton('_remove', '-')
            ->setHtmlAttribute('class', 'c-form__multiplier-remove-button')
            ->setHtmlAttribute('onclick', 'return ' . $uid . '_remove(this);');

        return $container;
    }

    /**
     * @return string
     */
    private function getUid(): string
    {
        if ($this->uid === null) {
            $this->uid = 'FormMultiplier_' . md5($this->form->getName() . '_' . md5($this->getHtmlId()));
        }

        return $this->uid;
    }
}
