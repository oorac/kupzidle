<?php

    namespace App\Modules\Admin\Presenters;

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
            $group->addItem($this->getPresenterInfo(), 'Feedy', 'Admin:Settings', 'Feed', 'default', 'ri-lg ri-dashboard-fill');
            $group->addItem($this->getPresenterInfo(), 'Produkty', 'Admin', 'Product', 'default', 'ri-lg ri-dashboard-fill');

            $group = $control->addGroup('Ostatní');

            $group->addItem($this->getPresenterInfo(), 'Adresář', 'Admin', 'Address', 'default', 'ri-lg ri-dashboard-fill');
            $group->addItem($this->getPresenterInfo(), 'Platební metody', 'Admin:List', 'PaymentMethod', 'default', 'ri-lg ri-dashboard-fill');
            $group->addItem($this->getPresenterInfo(), 'Dopravní metody', 'Admin:List', 'DeliveryMethod', 'default', 'ri-lg ri-dashboard-fill');
            $group->addItem($this->getPresenterInfo(), 'Poukazy', 'Admin:List', 'Voucher', 'default', 'ri-lg ri-dashboard-fill');
            $group->addItem($this->getPresenterInfo(), 'Sklady', 'Admin:List', 'Store', 'default', 'ri-lg ri-dashboard-fill');

            return $control;
        }
    }
