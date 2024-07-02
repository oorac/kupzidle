<?php declare(strict_types=1);

    namespace App\Services;

    use Nette\DI\Container;
    use Nette\DI\MissingServiceException;

    class DI
    {
        /**
         * @var self
         */
        private static self $instance;

        /**
         * @var Container
         */
        private Container $container;

        /**
         * @param Container $container
         */
        public function __construct(Container $container)
        {
            $this->container = $container;
            static::$instance = $this;
        }

        /**
         * @return static
         */
        public static function getInstance(): self
        {
            return static::$instance;
        }

        /**
         * @param string $class
         * @return object
         */
        public function get(string $class): object
        {
            return $this->container->getByType($class);
        }

        /**
         * @param string $name
         * @return object
         */
        public function getByName(string $name): object
        {
            if (!$service = $this->container->getByName($name)) {
                throw new MissingServiceException("Service of name '$name' not found. Check class name because it cannot be found.");
            }

            return $service;
        }

        /**
         * @return Container
         */
        public function getContainer(): Container
        {
            return $this->container;
        }
    }
