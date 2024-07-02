<?php declare(strict_types=1);

namespace App\Controls\Navigation;

use Nette\Application\UI\Control;

class Navigation extends Control
{
    /**
     * @var array|self[]
     */
    public array $groups = [];

    /**
     * @param string $title
     * @return Group
     */
    public function addGroup(string $title): Group
    {
        return $this->groups[] = new Group($title);
    }

    /**
     * @return void
     */
    public function render(): void
    {
        $this->template->groups = $this->groups;
        $this->template->setFile(__DIR__. '/Templates/default.latte');
        $this->template->render();
    }
}
