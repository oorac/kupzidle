<?php declare(strict_types=1);

namespace App\Controls\Navigation;

use App\Utils\LazyObject;

class SubItem
{
    /**
     * @var bool
     */
    private bool $active;

    /**
     * @var array|SubItem[]
     */
    private array $items = [];

    /**
     * @param LazyObject $presenterInfo
     * @param string $title
     * @param string $module
     * @param string $presenter
     * @param string $action
     * @param string $icon
     * @param int|null $param
     */
    public function __construct(
        private readonly LazyObject $presenterInfo,
        private readonly string $title,
        private readonly string $module,
        private readonly string $presenter,
        private readonly string $action,
        private readonly string $icon = '',
        private readonly ?int $param = null,
    ) {
        $this->active = $presenterInfo->get('module') === $this->module
            && $presenterInfo->get('name') === $this->presenter
            && $presenterInfo->get('action') === $this->action
            && (int) $presenterInfo->get('id') === $this->param;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getPresenter(): string
    {
        return $this->presenter;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return mixed
     */
    public function getParam(): mixed
    {
        return $this->param;
    }

    /**
     * @return bool
     */
    public function hasItems(): bool
    {
        return (bool) count($this->items);
    }

    /**
     * @return SubItem[]|array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param string $title
     * @param string $module
     * @param string $presenter
     * @param string $action
     * @param string $icon
     * @param int|null $param
     * @return $this
     */
    public function addItem(string $title, string $module, string $presenter, string $action, string $icon = '', ?int $param = null): self
    {
        $this->items[] = new SubItem($this->presenterInfo, $title, $module, $presenter, $action, $icon, $param);

        return $this;
    }
}
