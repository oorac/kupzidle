<?php declare(strict_types=1);

namespace App\Controls\Navigation;

final class Badge
{
    /**
     * @var string
     */
    private string $title;

    /**
     * @var string
     */
    private string $style;

    public function __construct(string $title, string $style)
    {
        $this->title = $title;
        $this->style = $style;
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
    public function getStyle(): string
    {
        return $this->style;
    }
}
