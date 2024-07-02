<?php declare(strict_types=1);

namespace App\Controls\Navigation;

use App\Utils\LazyObject;

final class Item
{
    /**
     * @var bool
     */
    private bool $active;

    /**
     * @var array|SubItem[]
     */
    public array $items = [];

    /**
     * @param LazyObject $presenterInfo
     * @param string $title
     * @param string $module
     * @param string $presenter
     * @param string $action
     * @param string $icon
     * @param int|null $param
     * @param Badge|null $badge
     */
    public function __construct(
        private readonly LazyObject $presenterInfo,
        private readonly string $title,
        private readonly string $module,
        private readonly string $presenter,
        private readonly string $action,
        private readonly string $icon = '',
        private readonly ?int $param = null,
        private readonly ?Badge $badge = null
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
        $active = false;
        if ($this->hasItems()) {
            foreach ($this->items as $item) {
                if ($item->isActive()) {
                    $active = true;
                }
            }
        }
        return ($this->active || ($active === true));
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
     * @return null|Badge
     */
    public function getBadge(): ?Badge
    {
        return $this->badge;
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
     * @param mixed $param
     * @return $this
     */
    public function addItem(string $title, string $module, string $presenter, string $action, string $icon = '', mixed $param = ''): self
    {
        $this->items[] = new SubItem($this->presenterInfo, $title, $module, $presenter, $action, $icon, $param);

        return $this;
    }
}
