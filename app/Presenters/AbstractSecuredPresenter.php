<?php declare(strict_types=1);

namespace App\Presenters;

use App\Controls\Breadcrumbs\Breadcrumbs;
use App\Controls\Breadcrumbs\IBreadcrumbsFactory;
use App\Doctrine\FormBuilder\BuilderFactory;
use App\Doctrine\ReverseFormMapper\Mapper;
use App\Helpers\UrlHelper;
use App\Models\Repositories\SettingsRepository;
use App\Models\Repositories\UserRepository;
use App\Models\User;
use App\Services\NotificationsService;
use Nette\Application\AbortException;

abstract class AbstractSecuredPresenter extends AbstractPresenter
{
    /**
     * @var User|null
     */
    public ?User $userEntity = null;

    /**
     * @var NotificationsService
     * @inject
     */
    public NotificationsService $notificationsService;

    /**
     * @var IBreadcrumbsFactory
     * @inject
     */
    public IBreadcrumbsFactory $breadcrumbsFactory;

    /**
     * @var UserRepository
     * @inject
     */
    public UserRepository $userRepository;

    /**
     * @var BuilderFactory
     * @inject
     */
    public BuilderFactory $builderFactory;

    /**
     * @var Mapper
     * @inject
     */
    public Mapper $mapper;

    /**
     * @var SettingsRepository
     * @inject
     */
    public SettingsRepository $settingsRepository;

    protected array $globalSettings = [];

    /**
     * @return void
     * @throws AbortException
     */
    protected function startup(): void
    {
        $this->securedAction();
        parent::startup();

        $this->template->identityUser = $this->getUserEntity();
        $this->template->notificationsCount = $this->notificationsService->count();
        $this->globalSettings = $this->settingsRepository->getPairs();
    }

    /**
     * @return float
     */
    protected function getCurrencyRate(): float
    {
        return (float) str_replace(',', '.', $this->globalSettings['currencyRate']);
    }

    /**
     * @return void
     * @throws AbortException
     */
    protected function securedAction(): void
    {
        if (! $this->user->isLoggedIn()
            || ! $this->user->getIdentity()
            || ! $this->user->getIdentity()->getUser()
        ) {
            UrlHelper::store('Login');
            $this->user->logout(true);
            $this->flashMessage('Je vyžadováno přihlášení', self::FM_ERROR);
            $this->redirect(':Front:Sign:in');
        }

        if ($this->user->getIdentity()->getUser()->isBlocked()) {
            $this->flashMessage($this->translator->translate('errors.yourAccountIsBlocked'), self::FM_ERROR);
            $this->redirect(':Front:Sign:in');
        }

        if (! $this->user->getIdentity()->getUser()->isActive()) {
            $this->flashMessage($this->translator->translate('messages.waitForAuthorization'), self::FM_WARNING);
            $this->redirect(':Front:Sign:in');
        }

        if (
            $this->user->isInRole(User::ROLE_GUEST)
            && $this->getPresenterInfo()->get('module') === 'App'
        ) {
            $this->flashMessage($this->translator->translate('errors.forbidden'), self::FM_ERROR);
            $this->redirect(':Front:Sing:in');
        }

        if (
            $this->getPresenterInfo()->get('module') !== 'App'
            && ! $this->getUser()->isInRole(User::ROLE_ADMIN)
        ) {
            $this->flashMessage($this->translator->translate('errors.forbidden'), self::FM_ERROR);
            $this->redirect(':App:Dashboard:');
        }
    }

    /**
    * @return User
    * @throws AbortException
    */
    protected function getUserEntity(): User
    {
        if (! $this->userEntity && ! $this->userEntity = $this->user->getIdentityUser()) {
            $this->redirect(':Front:Sign:in');
        }

        return $this->userEntity;
    }

    /**
     * @return Breadcrumbs
     */
    protected function createComponentBreadcrumbs(): Breadcrumbs
    {
        return $this->breadcrumbsFactory->create($this->getPresenterInfo());
    }
}
