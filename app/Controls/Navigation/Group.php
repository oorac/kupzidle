<?php declare(strict_types=1);

namespace App\Controls\Navigation;

use App\Utils\LazyObject;

final class Group
{
    /**
     * @var string
     */
    private string $title;

    /**
     * @var array|Item[]
     */
    public array $items = [];

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
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
     * @param LazyObject $presenterInfo
     * @param string $title
     * @param string $module
     * @param string $presenter
     * @param string $action
     * @param string $icon
     * @param int|null $param
     * @param Badge|null $badge
     * @return $this
     */
    public function addItem(LazyObject $presenterInfo, string $title, string $module, string $presenter, string $action, string $icon = '', ?int $param = null, ?Badge $badge = null): self
    {
        $this->items[] = new Item($presenterInfo, $title, $module, $presenter, $action, $icon, $param, $badge);

        return $this;
    }
}
