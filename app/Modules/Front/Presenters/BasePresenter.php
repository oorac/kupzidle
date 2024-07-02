<?php

    namespace App\Modules\Front\Presenters;

    use App\Controls\Navigation\Group;
    use App\Controls\Navigation\INavigationFactory;
    use App\Controls\Navigation\Navigation;
    use App\Doctrine\FormBuilder\BuilderFactory;
    use App\Doctrine\ReverseFormMapper\Mapper;
    use App\Models\Repositories\UserRepository;
    use App\Presenters\AbstractSecuredPresenter;

    abstract class BasePresenter extends AbstractSecuredPresenter
    {
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
         * @var INavigationFactory
         * @inject
         */
        public INavigationFactory $navigationFactory;

        /**
         * @var Mapper
         * @inject
         */
        public Mapper $mapper;

        /**
         * @return Navigation
         */
        protected function createComponentNavigation(): Navigation
        {
            $control = $this->navigationFactory->create();
            $group = $control->addGroup('');
            $group->addItem($this->getPresenterInfo(), 'Import dat', 'Admin', 'Dashboard', 'default', 'ri-lg ri-dashboard-fill');

            $group = new Group('Ostatní');
            $group->addItem($this->getPresenterInfo(), 'Zpět do aplikace', 'App', 'Dashboard', 'default', 'ri-file-transfer-line');
            $control->groups[] = $group;

            return $control;
        }
    }
