<?php declare(strict_types=1);

namespace App\Doctrine\FormBuilder;

use App\Services\DI;
use App\Services\Doctrine\EntityManager;
use Doctrine\Common\Annotations\AnnotationReader;
use Nette\Localization\Translator;

class BuilderFactory
{
    /**
     * @var AnnotationReader
     */
    private AnnotationReader $reader;

    /**
     * @param DI $di
     * @param EntityManager $entityManager
     * @param Translator $translator
     */
    public function __construct(
        private readonly DI $di,
        private readonly EntityManager $entityManager,
        private readonly Translator $translator
    ) {
        $this->reader = new AnnotationReader();
    }

    /**
     * @return Builder
     */
    public function getBuilder(): Builder
    {
        return new Builder(
            $this->di,
            $this->entityManager,
            $this->translator,
            $this->reader
        );
    }
}
