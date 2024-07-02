<?php

    declare(strict_types=1);

    namespace App\Modules\Front\Presenters;

    use App\Models\Repositories\UserRepository;
    use App\Models\User;
    use App\Presenters\AbstractPresenter;
    use App\Services\User\Password;
    use Nette\Application\AbortException;
    use Nette\Application\UI\Form;
    use Nette\Forms\IControl;
    use Nette\Security\AuthenticationException;
    use Nette\Security\User as SecurityUser;
    use Nette\Utils\ArrayHash;

    final class SignPresenter extends AbstractPresenter
    {
        /**
         * @var Password
         * @inject
         */
        public Password $password;

        /**
         * @var UserRepository
         * @inject
         */
        public UserRepository $userRepository;

        /**
         * @var SecurityUser
         * @inject
         */
        public SecurityUser $user;

        /**
         * @return never
         * @throws AbortException
         */
        public function actionOut(): never
        {
            $this->getUser()->logout();
            $this->flashMessage($this->translator->translate('messages.successLogout'), self::FM_INFO);
            $this->redirect(':Front:Sign:in');
        }

        /**
         * @return void
         * @throws AbortException
         */
        public function actionIn(): void
        {
            if ($this->user->isLoggedIn() && $this->user->isInRole(User::ROLE_ADMIN)) {
                $this->redirect(':Admin:Dashboard:');
            }
        }

        /**
         * @return Form
         */
        protected function createComponentSignInForm(): Form
        {
            $form = new Form();
            $form->addText('email', $this->translator->translate('forms.email'))
                ->setRequired($this->translator->translate('validations.email'));

            $form->addPassword('password', $this->translator->translate('forms.password'))
                ->setRequired($this->translator->translate('validations.password'));

            $form->addCheckbox('remember', $this->translator->translate('forms.remember'));

            $form->addSubmit('send', $this->translator->translate('actions.Login'));

            $form->onSuccess[] = [$this, 'processLogin'];

            return $form;
        }

        /**
         * @param Form $form
         * @param ArrayHash $values
         * @return void
         * @throws AbortException
         */
        public function processLogin(Form $form, ArrayHash $values): void
        {
            try {
                $this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
                $this->user->login($values->email, $values->password);
            } catch (AuthenticationException $exception) {
                $this->flashMessage($exception->getMessage(), self::FM_ERROR);
                $this->redirect('this');
            }

            if (
                $this->user->getIdentity()
                && $this->getUser()->isLoggedIn()
                && $this->getUser()->isInRole(User::ROLE_ADMIN)
            ) {
                $this->flashMessage($this->translator->translate('messages.welcomeInAdministration'));
                $this->redirect(':Admin:Dashboard:default');
            } elseif ($this->getUser()->isInRole(User::ROLE_USER)) {
                $this->flashMessage($this->translator->translate('messages.waitForAuthorization'), self::FM_WARNING);
                $this->redirect(':in');
            }
        }

        /**
         * Sign-up form factory.
         */
        protected function createComponentSignUpForm(): Form
        {
            $form = new Form();

            $form->addText('email', $this->translator->translate('forms.email') . ' *')
                ->setRequired($this->translator->translate('validations.email'))
                ->setHtmlAttribute('placeholder', $this->translator->translate('forms.placeholder.email'));
            $form->addPassword('password', $this->translator->translate('forms.password') . ' *')
                ->setRequired($this->translator->translate('validations.password'))
                ->setHtmlAttribute('placeholder', $this->translator->translate('forms.placeholder.password'))
                ->addRule(function (IControl $control) {
                    $password = $control->getValue();
                    $containsUppercase = preg_match('/[A-Z]/', $password);
                    $containsLowercase = preg_match('/[a-z]/', $password);
                    $containsNumber = preg_match('/\d/', $password);
                    $isLongEnough = strlen($password) >= User::PASSWORD_MIN_LENGTH;

                    return $containsUppercase && $containsLowercase && $containsNumber && $isLongEnough;
                }, $this->translator->translate('errors.registerForm.password'));

            $form->addSubmit('send', $this->translator->translate('actions.Register'));

            $form->onSuccess[] = [$this, 'processRegister'];
            return $form;
        }


        /**
         * @param Form $form
         * @return never
         * @throws AbortException
         */
        public function processRegister(Form $form): never
        {
            $data = $form->getValues();

            if ($this->userRepository->findOneBy(['email' => $data->email])) {
                $this->flashMessage($this->translator->translate('errors.accountAlreadyExist'), self::FM_ERROR);
                $this->redirect(':up');
            }

            $user = (new User())
                ->setEmail($data->email)
                ->setPassword($this->password->hash($data->password))
                ->setRole(User::ROLE_USER);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->flashMessage($this->translator->translate('messages.successRegister'), self::FM_INFO);
            $this->redirect(':in');
        }
    }
