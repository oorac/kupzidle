<?php

namespace App\Modules\Admin\Modules\List\Presenters;

use App\Forms\Form;
use App\Models\Repositories\UserRepository;
use App\Models\User;
use App\Modules\Admin\Presenters\BasePresenter;
use DateTime;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\Exception\DataGridException;

final class UserPresenter extends BasePresenter
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @var UserRepository
     * @inject
     */
    public UserRepository $userRepository;

    /**
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = new Form();

        $form->addHidden('id');
        $form->addText('firstName', $this->translator->translate('forms.firstName') . ' *')
            ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        $form->addText('lastName', $this->translator->translate('forms.lastName') . ' *')
            ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        $form->addText('email', $this->translator->translate('forms.email') . ' *')
            ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        $form->addText('phone', $this->translator->translate('forms.phone') . ' *')
            ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        $form->addRadioList('sex', $this->translator->translate('forms.sex') . ' *', User::TYPE_SEX)
            ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        $form->addSelect('role', $this->translator->translate('forms.role') . ' *', User::TYPE_ROLES)
            ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        $form->addCheckbox('active', $this->translator->translate('forms.active'));
        $form->addCheckbox('blocked', $this->translator->translate('forms.blocked'));

        $form->addSubmit('submit', $this->translator->translate('actions.Save'));

        $form->onSuccess[] = [$this, 'handleUpdate'];

        return $form;
    }

    /**
     * @param int $id
     * @return void
     * @throws BadRequestException
     */
    public function actionEdit(int $id): void
    {
        if (! $user = $this->userRepository->find($id)) {
            $this->error($this->translator->translate('errors.UserDoesntExists'), 404);
        }

        $this->user = $user;

        $this->getComponent('form')->setDefaults([
            'id' => $this->user->getId(),
            'firstName' => $this->user->getFirstname(),
            'lastName' => $this->user->getLastname(),
            'email' => $this->user->getEmail(),
            'phone' => $this->user->getPhone(),
            'sex' => $this->user->getSex(),
            'role' => $this->user->getRole(),
            'active' => $this->user->isActive(),
            'blocked' => $this->user->isBlocked()
        ]);
    }

    /**
     * @param Form $form
     * @return never
     * @throws AbortException
     */
    public function handleUpdate(Form $form): never
    {
        $data = $form->getValues();
        if (! $data->id) {
            $user = new User();
            $this->entityManager->persist($user);
            $this->user = $user;
        }

        $this->user->setBlocked($data->blocked ? (new DateTime()) : NULL)
            ->setFirstname($data->firstName)
            ->setLastname($data->lastName)
            ->setPhone($data->phone)
            ->setRole($data->role)
            ->setSex($data->sex);

        if ($data->active) {
            $this->user->setActivateOn();
        }

        $this->entityManager->flush();

        $this->flashMessage($this->translator->translate('messages.Saved'));
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
            ->select('user')
            ->from(User::class, 'user');

        $grid->setDataSource(new DoctrineDataSource($queryBuilder, 'id'));

        $grid->addColumnText('name', $this->translator->translate('forms.name'))
            ->setRenderer(function (User $user) {
                return $user->getFullName();
            })
            ->setFilterText();

        $grid->addColumnText('email', $this->translator->translate('forms.email'))
            ->setRenderer(function (User $user) {
                return $user->getEmail();
            })
            ->setFilterText();

        $grid->addColumnText('phone', $this->translator->translate('forms.phone'))
            ->setFilterText();

        $grid->addColumnText('blocked', $this->translator->translate('forms.blocked'))
            ->setRenderer(function (User $user) {
                return $user->isBlocked() ? $this->translator->translate('texts.Yes') : $this->translator->translate('texts.No');
            })
            ->setFilterSelect([
                '' => $this->translator->translate('texts.-all-'),
                1 => $this->translator->translate('texts.Yes'),
                0 => $this->translator->translate('texts.No'),
            ]);

        $grid->addColumnText('activateOn', $this->translator->translate('forms.activated'))
            ->setRenderer(function (User $user) {
                return $user->isActive() ? $this->translator->translate('texts.Yes') : $this->translator->translate('texts.No');
            })
            ->setFilterSelect([
                '' => $this->translator->translate('texts.-all-'),
                1 => $this->translator->translate('texts.Yes'),
                0 => $this->translator->translate('texts.No'),
            ]);

        $grid->addColumnText('role', $this->translator->translate('forms.role'))
            ->setRenderer(function (User $user) {
                return match ($user->getRole()) {
                    USER::ROLE_USER => $this->translator->translate('texts.roles.' . User::ROLE_USER),
                    USER::ROLE_CASHIER => $this->translator->translate('texts.roles.' . User::ROLE_CASHIER),
                    USER::ROLE_MANAGER => $this->translator->translate('texts.roles.' . User::ROLE_MANAGER),
                    USER::ROLE_ADMIN => $this->translator->translate('texts.roles.' . User::ROLE_ADMIN)
                };
            })
            ->setFilterSelect([
                '' => $this->translator->translate('texts.-all-'),
                USER::ROLE_USER => $this->translator->translate('texts.roles.' . User::ROLE_USER),
                USER::ROLE_CASHIER => $this->translator->translate('texts.roles.' . User::ROLE_CASHIER),
                USER::ROLE_MANAGER => $this->translator->translate('texts.roles.' . User::ROLE_MANAGER),
                USER::ROLE_ADMIN => $this->translator->translate('texts.roles.' . User::ROLE_ADMIN),
            ]);

        $grid->addAction(':edit', $this->translator->translate('actions.Edit'));
        $grid->addAction(':rules', $this->translator->translate('actions.Rules'));
        $grid->addAction(':blocked', $this->translator->translate('actions.Blocked'))
            ->setRenderCondition(function (User $user){
                return ! $user->isBlocked() && $this->userEntity !== $user;
            })
            ->setClass('btn btn-danger waves-effect waves-light');

        $grid->addAction(':unblocked', $this->translator->translate('actions.UnBlocked'))
            ->setRenderCondition(function (User $user){
                return $user->isBlocked();
            })
            ->setClass('btn btn-success waves-effect waves-light');

        return $grid;
    }

    /**
     * @param int $id
     * @return void
     * @throws BadRequestException
     */
    public function actionRules(int $id): void
    {
        if (! $this->user = $this->userRepository->find($id)) {
            $this->error($this->translator->translate('errors.UserDoesntExists'), 404);
        }

        $form = $this->getComponent('rulesForm');
        $establishments = $this->establishmentRepository->findAll();
        $cashRegistersRules = [];
        foreach ($establishments as $establishment) {
            $cashRegisters = $establishment->getCashRegisters();
            foreach ($cashRegisters as $cashRegister) {
                foreach ($cashRegister->getUsers() as $user) {
                    if ($user === $this->user) {
                        $cashRegistersRules['establishment_' . $establishment->getId()][] = $cashRegister->getId();
                    }
                }
            }
        }
        $form->setDefaults([
            'cashRegisters' => json_encode($cashRegistersRules, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
        ]);

        $this->template->userEntity = $this->user;
    }

    /**
     * @return Form
     */
    protected function createComponentRulesForm(): Form
    {
        $form = new Form;

        $establishments = $this->establishmentRepository->findAll();
        foreach ($establishments as $establishment) {
            $container = $form->addContainer($establishment->getId());
            $container->addMultiSelect('cashRegisters', $establishment->getTitle(), $this->cashRegisterRepository->getPairsByEstablishment($establishment))
                ->setHtmlId('establishment_' . $establishment->getId())
                ->setHtmlAttribute('class', 'selectbox');
        }

        $form->addHidden('cashRegisters')
            ->setHtmlId('cashRegisters');

        $form->addSubmit('submit', $this->translator->translate('actions.Save'));

        $form->onSuccess[] = [$this, 'processRules'];

        return $form;
    }

    /**
     * @param Form $form
     * @return never
     * @throws AbortException
     */
    public function processRules(Form $form): never
    {
        $data = $form->getValues();

        unset($data['cashRegisters']);

        $cashRegisterEntities = $this->cashRegisterRepository->findAll();

        foreach ($cashRegisterEntities as $cashRegisterEntity) {
            $cashRegisterEntity->removeUser($this->user);
        }

        foreach ($data as $cashRegisters) {
            foreach ($cashRegisters as $cashRegister) {
                foreach ($cashRegister as $item) {
                    if (! $cashRegisterEntity = $this->cashRegisterRepository->find($item)) {
                        $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
                        $this->redirect(':');
                    }

                    $cashRegisterEntity->addUser($this->user);
                }
            }
        }

        $this->entityManager->flush();

        $this->flashMessage($this->translator->translate('messages.RowWasEdited'));
        $this->redirect(':');
    }

    /**
     * @param int $id
     * @return never
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionBlocked(int $id): never
    {
        if (! $user = $this->userRepository->find($id)) {
            $this->error($this->translator->translate('errors.UserDoesntExists'), 404);
        }

        $user->setBlocked(new DateTime());
        $this->entityManager->flush();
        $this->flashMessage($this->translator->translate('messages.UserWasBlocked'));
        $this->redirect(':default');
    }

    /**
     * @param int $id
     * @return never
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionUnBlocked(int $id): never
    {
        if (! $user = $this->userRepository->find($id)) {
            $this->error($this->translator->translate('errors.UserDoesntExists'), 404);
        }

        $user->setBlocked(null);
        $this->entityManager->flush();
        $this->flashMessage($this->translator->translate('messages.UserWasUnBlocked'));
        $this->redirect(':default');
    }
}
