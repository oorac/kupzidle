<?php declare(strict_types=1);

    namespace App\Services;

    use App\Exceptions\NotFoundException;
    use App\Media\Storages\Image\LocalImageMediaStorage;
    use App\Services\Deadpool\Processor;
    use InvalidArgumentException;

    class ApplicationService
    {
        /**
         * @var bool
         */
        private static bool $terminated = false;

        /**
         * @param Processor $processor
         */
        public function __construct(
            private readonly Processor $processor,
        )
        {
        }

        /**
         * @return void
         */
        public function onStartup(): void
        {
            register_shutdown_function(function () {
                $this->onShutdown();
            });

            if (PHP_SAPI !== 'cli') {
                $this->handleLocalImageProcessing();
            }
        }

        /**
         * @return void
         */
        private function onShutdown(): void
        {
            if (!self::$terminated) {
                self::$terminated = true;

//            // when root, fix permissions
//            if (posix_geteuid() === 0) {
//                Shell::execToNull('cd ' . DIR_ROOT . ' && bash bin/chown.sh');
//            }
//
//                $this->bunny->consumeParallel();
            }
        }

        /**
         * @return void
         */
        private function handleLocalImageProcessing(): void
        {
            try {
                if ($arguments = LocalImageMediaStorage::parseResizeUrlParameters()) {
                    $this->processor->processLocalRequest($arguments);
                    exit();
                }
            } catch (InvalidArgumentException|NotFoundException) {
                http_response_code(404);
                exit();
            }
        }
    }
