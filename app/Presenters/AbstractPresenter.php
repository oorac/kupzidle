<?php declare(strict_types=1);

namespace App\Presenters;

use App\Providers\SettingsProvider;
use App\Security\SecurityUser;
use App\Services\Deadpool\Deadpool;
use App\Services\Doctrine\EntityManager;
use App\Utils\Arrays;
use App\Utils\LazyObject;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Localization\Translator;
use stdClass;
use Ublaboo\DataGrid\DataGrid;

/**
 * @property SecurityUser $user
 */
abstract class AbstractPresenter extends Presenter
{
    public const FM_SUCCESS = 'success';
    public const FM_INFO = 'info';
    public const FM_WARNING = 'warning';
    public const FM_ERROR = 'danger';

    protected string $title = '';
    protected string $description = '';
    protected string $keywords = '';
    protected ?string $backlink = null;

    /**
     * @var SettingsProvider
     * @inject
     */
    public SettingsProvider $settingsProvider;

    /**
     * @var EntityManager
     * @inject
     */
    public EntityManager $entityManager;

    /**
     * @var Translator
     * @inject
     */
    public Translator $translator;

    /**
     * @var Deadpool
     * @inject
     */
    public Deadpool $deadpool;

    /**
     * @var LazyObject|null
     */
    private ?LazyObject $info = null;

    /**
     * @param $message
     * @param string $type
     * @return stdClass
     */
    public function flashMessage($message, string $type = self::FM_SUCCESS): stdClass
	{
        return parent::flashMessage($message, $type);
	}

    /**
     * @param string $message
     * @param string $type
     * @param ...$parameters
     * @return stdClass
     */
    public function flashTranslatedMessage(string $message, string $type = self::FM_SUCCESS, ... $parameters): stdClass
	{
        return parent::flashMessage($this->translator->translate($message, ... $parameters), $type);
	}

    /**
     * @return never
     * @throws BadRequestException
     */
    public function errorForbidden(): never
    {
        $this->error('Přístup zamítnut', IResponse::S403_Forbidden);
    }

    /**
     * @return LazyObject
     */
    public function getPresenterInfo(): LazyObject
    {
        if (! $this->info) {
            $this->info = new LazyObject([
                'module' => function (LazyObject $object) {
                    $chunks = Arrays::reverse(explode(':', $this->getName()));
                    $object->set('name', $chunks[0]);

                    return $chunks[1] ?? null;
                },
                'name' => function (LazyObject $object) {
                    $chunks = Arrays::reverse(explode(':', $this->getName()));
                    $object->set('module', $chunks[1] ?? null);

                    return $chunks[0] ?? null;
                },
                'action' => function () {
                    return $this->getAction();
                },
                'id' => function () {
                    return $this->getParameter('id');
                },
                'fullName' => function () {
                    return $this->getName() . ':' . $this->getAction();
                }
            ]);
        }

        return $this->info;
    }

    /**
     * @return void
     */
    protected function beforeRender(): void
    {
        parent::beforeRender();
        $this->backlink = $this->storeRequest();
        $this->template->title = $this->translator->translate('pages.' . $this->getPresenterInfo()->get('fullName'));
        $this->template->deadpool = $this->deadpool;
        $this->template->siteName = $this->settingsProvider->getString('siteName');
        $this->template->description = $this->description ?: '';
        $this->template->keywords = $this->keywords ?: '';
    }

    /**
     * @param string $destination
     * @param array $arguments
     * @return void
     * @throws AbortException
     */
    protected function requireConfirmation(string $destination, array $arguments = []): void
    {
        if (empty($_GET['confirmed'])) {
            $this->flashMessage('Je vyžadováno potvrzení akce', self::FM_ERROR);
            $this->redirect($destination, $arguments);
        }
    }

    /**
     * @param string $name
     * @return DataGrid
     */
    protected function createDatagrid(string $name): Datagrid
    {
        $grid = new Datagrid($this, $name);
        $grid->setTranslator($this->translator);
        $grid->setItemsPerPageList([10, 50, 100, 500], false);
        $grid->setColumnsHideable();
        $grid->setDefaultPerPage(10);
        $grid->setTemplateFile(DIR_APP . DS . 'Templates/Datagrid/templates/custom.latte');

        return $grid;
    }
}
