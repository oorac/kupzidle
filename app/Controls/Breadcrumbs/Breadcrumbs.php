<?php declare(strict_types=1);

namespace App\Controls\Breadcrumbs;

use App\Models\Repositories\CashRegisterRepository;
use App\Utils\LazyObject;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\Control;
use Nette\Application\UI\InvalidLinkException;
use Nette\Localization\Translator;

class Breadcrumbs extends Control
{
    /**
     * @param LazyObject $presenterInfo
     * @param Translator $translator
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(
        private readonly LazyObject $presenterInfo,
        private readonly Translator $translator,
        private readonly LinkGenerator $linkGenerator
    ) {}

    /**
     * @return void
     * @throws InvalidLinkException
     */
    public function render(): void
    {
        $this->template->items = $this->generate();
        $this->template->setFile(__DIR__. '/Templates/default.latte');
        $this->template->render();
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    private function generate(): array
    {
        if ($this->presenterInfo->get('module') === 'App') {
            return $this->generateApp();
        }

        if ($this->presenterInfo->get('module') === 'Admin') {
            return $this->generateAdmin();
        }

        if (str_contains($this->presenterInfo->get('fullName'), 'Admin')) {
            return $this->generateAdmin();
        }

        if (str_contains($this->presenterInfo->get('fullName'), 'App')) {
            return $this->generateApp();
        }

        return [];
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    private function generateApp(): array
    {
        $isDashboard = $this->presenterInfo->get('name') === 'Dashboard';

        $list = [];
        $list[] = [
            'url' => $isDashboard ? '#' : $this->linkGenerator->link('App:Dashboard:default'),
            'title' => $isDashboard ? 'Vítejte v Solitaire' : 'Solitaire',
            'icon' => 'ri-home-5-fill'
        ];

        if ($isDashboard) {
            return $list;
        }

        $this->appendCurrentCashRegisterPage($list);
        $this->appendCurrentCashRegisterItemPage($list);
        $this->appendCurrentFixPage($list, 'App');
        $this->appendCurrentSafePage($list, 'App');
        $this->appendCurrentPage($list);

        return $list;
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    private function generateAdmin(): array
    {
        $list = [];
        $list[] = [
            'url' => $this->linkGenerator->link('Admin:Dashboard:default'),
            'title' => $this->translator->translate('pages.Admin:Dashboard:default'),
        ];

        if ($this->presenterInfo->get('name') === 'Dashboard') {
            return $list;
        }

        $this->appendCurrentUserPage($list);
        $this->appendCurrentGlobalSettingsPage($list);

        $this->appendCurrentPage($list);

        return $list;
    }

    /**
     * @param array $list
     * @return void
     * @throws InvalidLinkException
     */
    private function appendCurrentUserPage(array &$list): void
    {
        if ($this->presenterInfo->get('name') === 'User' && $this->presenterInfo->get('action') !== 'default') {
            $list[] = [
                'url' => $this->linkGenerator->link('Admin:List:User:default'),
                'title' => $this->translator->translate('pages.Admin:List:User:default'),
            ];
        }
    }

    /**
     * @param array $list
     * @return void
     * @throws InvalidLinkException
     */
    private function appendCurrentGlobalSettingsPage(array &$list): void
    {
        if ($this->presenterInfo->get('name') === 'Global' && $this->presenterInfo->get('action') !== 'default') {
            $list[] = [
                'url' => $this->linkGenerator->link('Admin:Settings:Global:default'),
                'title' => $this->translator->translate('pages.Admin:Settings:Global:default'),
            ];
        }
    }

    /**
     * @param array $list
     * @return void
     */
    private function appendCurrentPage(array &$list): void
    {
        $name = $this->presenterInfo->get('name');
        $module = $this->presenterInfo->get('module');
        $action = $this->presenterInfo->get('action');

        if ($action !== 'default') {
            $list = $this->extracted($module, $name, $action, $list);
        }

        if (! str_ends_with($name, 's')) {
            $newName = $name . 's';
            $list = $this->extracted($module, $newName, $action, $list);
        }

        $list[] = [
            'url' => null,
            'title' => $this->translator->translate('pages.' . $this->presenterInfo->get('fullName')),
            'active' => $this->presenterInfo->get('module') === $module
                && $this->presenterInfo->get('name') === $name
                && $this->presenterInfo->get('action') === $action
        ];
    }

    /**
     * @param mixed $module
     * @param mixed $name
     * @param mixed $action
     * @param array $list
     * @return array
     */
    private function extracted(mixed $module, mixed $name, mixed $action, array &$list): array
    {
        try {
            $destination = $module . ':' . $name . ':default';
            if ($this->translator->hasTranslation('pages.' . $destination)) {
                $list[] = [
                    'url' => $this->linkGenerator->link($destination),
                    'title' => $this->translator->translate('pages.' . $destination),
                    'active' => $this->presenterInfo->get('module') === $module
                        && $this->presenterInfo->get('name') === $name
                        && $this->presenterInfo->get('action') === $action
                ];
            }
        } catch (InvalidLinkException) {
        }
        return $list;
    }
}
