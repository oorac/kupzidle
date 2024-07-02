<?php declare(strict_types=1);

namespace App\Services\Doctrine;

use App\Models\Interfaces\IEntityOnDestroyCallback;
use App\Models\Interfaces\IEntityOnStoreCallback;
use App\Services\DI;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Throwable;

class EntityManager extends EntityManagerDecorator
{
    /**
     * @var self $this
     */
    private static self $instance;

    /**
     * @var bool
     */
    private bool $flushing = false;

    /**
     * @var bool
     */
    private bool $callEvents = true;

    /**
     * @var array
     */
    private array $scheduledEntityTouches = [];

    /**
     * @var array
     */
    private array $postFlushStack = [];

    /**
     * @param EntityManagerInterface $wrapped
     * @param DI $di
     */
    public function __construct(EntityManagerInterface $wrapped, private readonly DI $di)
    {
        parent::__construct($wrapped);
        self::$instance = $this;
        $this->wrapped->getEventManager()->addEventListener([Events::preFlush], $this);
    }

    /**
     * Use only in extreme cases where it is not possible to use a DI container!
     * @return static
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * @param object $object
     * @return object
     */
    public function persist(object $object): object
    {
        parent::persist($object);

        if ($this->flushing) {
            $this->scheduledEntityTouches[] = $object;
        }

        return $object;
    }

    /**
     * @param null $entity
     */
    public function flush($entity = null): void
    {
        if ($this->flushing) {
            return;
        }

        $this->flushing = true;
        $this->callEvents = true;
        parent::flush($entity);
        $this->flushing = false;

        $this->processPostFlushStack();
    }

    /**
     * @return void
     */
    public function flushWithoutCallingEvents(): void
    {
        if ($this->flushing) {
            return;
        }

        $this->flushing = true;
        $this->callEvents = false;
        parent::flush();
        $this->flushing = false;
        $this->callEvents = true;

        $this->processPostFlushStack();
    }

    /**
     * @param $entity
     * @return $this
     */
    public function touch($entity): self
    {
        $this->scheduledEntityTouches[] = $entity;

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function preFlush(): void
    {
        if (! $this->callEvents) {
            return;
        }

        $processed = [];
        $unit = clone $this->getUnitOfWork();
        $unit->computeChangeSets();

        foreach ($unit->getScheduledEntityInsertions() as $entity) {
            if (
                ($entity instanceof IEntityOnStoreCallback)
                && ! in_array($entity, $processed, true)
                && is_callable($callback = $entity->onStore($this->di))
            ) {
                $this->postFlushStack[] = $callback;
                $processed[] = $entity;
            }
        }

        foreach ($unit->getScheduledEntityUpdates() as $entity) {
            if (
                ($entity instanceof IEntityOnStoreCallback)
                && ! in_array($entity, $processed, true)
                && is_callable($callback = $entity->onStore($this->di))
            ) {
                $this->postFlushStack[] = $callback;
                $processed[] = $entity;
            }
        }

        foreach ($unit->getScheduledEntityDeletions() as $entity) {
            if (
                ($entity instanceof IEntityOnDestroyCallback)
                && ! in_array($entity, $processed, true)
                && is_callable($callback = $entity->onDestroy($this->di))
            ) {
                $this->postFlushStack[] = $callback;
                $processed[] = $entity;
            }
        }

        while ($entity = $this->getEntityFromPostFlushStack()) {
            if (
                ($entity instanceof IEntityOnStoreCallback)
                && ! in_array($entity, $processed, true)
                && is_callable($callback = $entity->onStore($this->di))
            ) {
                $this->postFlushStack[] = $callback;
                $processed[] = $entity;
            }
        }
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function pushToPostFlushStack(callable $callback): self
    {
        $this->postFlushStack[] = $callback;

        return $this;
    }

    /**
     * @return object|null
     */
    private function getEntityFromPostFlushStack(): ?object
    {
        return array_shift($this->scheduledEntityTouches);
    }

    /**
     * @return void
     */
    private function processPostFlushStack(): void
    {
        $stack = $this->postFlushStack;
        $this->postFlushStack = [];

        foreach ($stack as $callback) {
            $callback();
        }
    }
}
