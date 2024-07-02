<?php declare(strict_types = 1);

namespace App\Controls;

use Nette\Application\UI\Control;

class BaseControl extends Control
{
    protected string $templatePath = "";

    /**
     * @return string
     */
    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * @param string $templatePath
     * @return $this
     */
    public function setTemplatePath(string $templatePath): self
    {
        $this->templatePath = $templatePath;

        return $this;
    }
}