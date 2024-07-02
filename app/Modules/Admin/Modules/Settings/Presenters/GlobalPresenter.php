<?php

namespace App\Modules\Admin\Modules\Settings\Presenters;

use App\Forms\Form;
use App\Helpers\UrlHelper;
use App\Models\Repositories\SettingsRepository;
use App\Models\Settings;
use App\Modules\Admin\Presenters\BasePresenter;
use App\Utils\Strings;
use Nette\Application\AbortException;
use ReflectionException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\Exception\DataGridException;

final class GlobalPresenter extends BasePresenter
{
    /**
     * @var SettingsRepository
     * @inject
     */
    public SettingsRepository $settingsRepository;

    /**
     * @var null|Settings
     */
    private ?Settings $settings = null;

    /**
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = new Form;

        $form->addText('title', $this->translator->translate('forms.title') .  ' *')
            ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        $form->addSelect('type', $this->translator->translate('forms.type'), Settings::LIST_TYPES);
        $form->addText('value', $this->translator->translate('forms.value') .  ' *')
            ->setRequired($this->translator->translate('messages.ThisIsRequired'))
            ->addConditionOn($form['type'], Form::EQUAL, Settings::TYPE_NUMBER)
            ->addFilter([Strings::class, 'filterNumbersOnly']);

        $form->addSubmit('submit', $this->translator->translate('actions.Save'));

        $form->onSuccess[] = [$this, 'handleUpdate'];

        return $form;
    }

    /**
     * @param int $id
     * @return void
     * @throws AbortException
     */
    public function actionEdit(int $id): void
    {
        if (! $this->settings = $this->settingsRepository->find($id)) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redirect(':');
        }

        $form = $this->getComponent('form');
        $form->setDefaults([
            'title' => $this->settings->getTitle(),
            'type' => $this->settings->getType(),
            'value' => $this->settings->getValue()
        ]);

        if ($this->settings->isDefault()) {
            $form['title']->setHtmlAttribute('readonly', 'readonly');
        }
    }

    /**
     * @param Form $form
     * @return never
     * @throws AbortException
     */
    public function handleUpdate(Form $form): never
    {
        $data = $form->getValues();
        if ($this->settings === null) {
            $this->settings = new Settings($data->title, $data->type, $data->value);
            $this->entityManager->persist($this->settings);
        } else {
            $this->settings->setValue($data->value)
                ->setTitle($data->title)
                ->setType($data->type);
        }
        $this->entityManager->flush();

        $this->flashMessage($this->translator->translate('messages.RowWasEdited'));
        if ($url = UrlHelper::restore('AddressPostCreate')) {
            $this->redirectUrl($url);
        }
        $this->redirect(':');
    }

    /**
     * @param string $name
     * @return DataGrid
     * @throws DataGridException
     */
    protected function createComponentListGrid(string $name): Datagrid
    {
        $grid = $this->createDatagrid($name);

        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('settings')
            ->from(Settings::class, 'settings');

        $grid->setDataSource(new DoctrineDataSource($queryBuilder, 'id'));

        $grid->addColumnText('title', $this->translator->translate('forms.title'))
            ->setRenderer(function (Settings $settings) {
                return $this->translator->translate('forms.' . $settings->getTitle());
            })
            ->setFilterText();

        $grid->addColumnText('type', $this->translator->translate('forms.type'))
            ->setRenderer(function (Settings $settings) {
                return $this->translator->translate('forms.types.' . $settings->getType());
            })
            ->setFilterText();

        $grid->addColumnText('value', $this->translator->translate('forms.value'))
            ->setFilterText();

        $grid->addAction(':edit', $this->translator->translate('actions.Edit'));

        return $grid;
    }
}
