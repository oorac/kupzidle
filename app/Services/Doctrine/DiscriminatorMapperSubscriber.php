<?php declare(strict_types=1);

namespace App\Services\Doctrine;

use App\Helpers\ClassesHelper;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Nette\NotSupportedException;
use ReflectionClass;
use ReflectionException;

class DiscriminatorMapperSubscriber implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $args
     * @throws NotSupportedException
     * @throws ReflectionException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $meta = $args->getClassMetadata();
        if ($meta->isInheritanceTypeSingleTable()) {

            $map = [];
            foreach (ClassesHelper::getAll() as $class) {
                if (is_subclass_of($class, $meta->getName())) {
                    $reflection = new ReflectionClass($class);
                    $map[$reflection->getShortName()] = $reflection->getName();
                }
            }

            $meta->setDiscriminatorMap($map);
        }
    }
}
