<?php declare(strict_types=1);

namespace App\Doctrine\ReverseFormMapper;

use App\Doctrine\ReverseFormMapper\Exceptions\MapperException;
use App\Exceptions\NotFoundException;
use App\Forms\Form;
use App\Helpers\CryptoHelper;
use App\Media\DataMedium;
use App\Models\File;
use App\Models\Image;
use App\Models\Interfaces\IEntity;
use App\Services\Doctrine\EntityManager;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Exception;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;
use ReflectionException;
use ReflectionProperty;
use Tracy\Debugger;

class Mapper
{
    /**
     * @var AnnotationReader
     */
    private AnnotationReader $reader;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        private readonly EntityManager $entityManager,
    ) {
        $this->reader = new AnnotationReader();
    }

    /**
     * @param IEntity $entity
     * @param Form $form
     * @return IEntity
     * @throws ReflectionException
     */
    public function mapForm(IEntity $entity, Form $form): IEntity
    {
        $http = $form->getHttpData();
        $data = $form->getValues(true);

        $this->bind($entity, $data, $form);
        $this->entityManager->persist($entity);

        if (method_exists($entity, 'setUpdatedOnNow')) {
            $entity->setUpdatedOnNow();
        }

        if (! empty($http['_postSave'])) {
            $this->processPostSave($entity, $http['_postSave']);
        }

        return $entity;
    }

    /**
     * @param IEntity $entity
     * @param array $data
     * @return IEntity
     * @throws ReflectionException
     */
    public function mapData(IEntity $entity, array $data): IEntity
    {
        $this->bind($entity, $data, null);
        $this->entityManager->persist($entity);

        if (method_exists($entity, 'setUpdatedOnNow')) {
            $entity->setUpdatedOnNow();
        }

        return $entity;
    }

    /**
     * @param IEntity $entity
     * @param array $data
     * @param Form|null $form
     * @throws ReflectionException
     */
    private function bind(IEntity $entity, array $data, ?Form $form): void
    {
        $this->mapEntity($entity, $data, $form, '');
        $this->entityManager->touch($entity);
    }

    /**
     * @param IEntity $entity
     * @param array $data
     * @param Form|null $form
     * @return void
     * @throws ReflectionException
     */
    private function mapEntity(IEntity $entity, array $data, ?Form $form): void
    {
        foreach ($entity->_getEntityProperties() as $property => $value) {

            if (! isset($data[$property])) {
                continue;
            }

            $reflection = new ReflectionProperty($entity::_getEntityClassName(), $property);
            $annotations = $this->reader->getPropertyAnnotations($reflection);
            $annotation = reset($annotations);

            if (! $annotation) {
                continue;
            }

            if ($annotation instanceof Column) {
                $this->mapColumn($entity, $annotation, $property, $data[$property], $form);
                continue;
            }

            if ($annotation instanceof OneToOne) {
                $this->mapOneToOne($entity, $annotation, $property, $data[$property]);
                continue;
            }

            if ($annotation instanceof OneToMany) {
                $this->mapOneToMany($entity, $annotation, $property, $data[$property]);
                continue;
            }

            if ($annotation instanceof ManyToOne) {
                $this->mapManyToOne($entity, $annotation, $property, $data[$property]);
                continue;
            }

            if ($annotation instanceof ManyToMany) {
                $this->mapManyToMany($entity, $annotation, $property, $data[$property]);
                continue;
            }

            if ($annotation instanceof Id) {
                continue;
            }

            throw new MapperException(
                sprintf('I don\'t know what to do with annotation of "%s" type. Annotation for "%s::$%s". Info:',
                    get_class($annotation),
                    get_class($entity),
                    $property
                )
            );
        }
    }

    /**
     * @param IEntity $entity
     * @param Column $annotation
     * @param string $property
     * @param $value
     * @param Form|null $form
     * @return void
     */
    private function mapColumn(IEntity $entity, Column $annotation, string $property, $value, ?Form $form): void
    {
        switch ($annotation->type) {
            case 'string':
            case 'text':
                $value = trim((string) $value);
                if ($value === '<p>&nbsp;</p>') {
                    $entity->_setEntityProperty($property, '');

                    return;
                }

                $entity->_setEntityProperty($property, $value);

                return;

            case 'boolean':
                $entity->_setEntityProperty($property, (bool) $value);

                return;

            case 'integer':
                $entity->_setEntityProperty($property, (int) $value);

                return;

            case 'decimal':
            case 'float':
                $entity->_setEntityProperty($property, (float) $value);

                return;

            case 'datetime':
            case 'date':
                if ($value) {
                    try {
                        $value = new DateTime($value);
                    } catch (Exception) {
                        $value = null;
                    }
                } else {
                    $value = null;
                }

                $entity->_setEntityProperty($property, $value);

                return;

            case 'enum':
                $entity->_setEntityProperty($property, $value);

                return;

            case 'json':
            case 'simple_array':
            case 'lazy_json_array':
                $entity->_setEntityProperty($property, (array) $value);

                return;
        }

        throw new MapperException('Undefined column type "' . $annotation->type . '"');
    }

    /**
     * @param IEntity $entity
     * @param OneToOne $annotation
     * @param string $property
     * @param $value
     */
    private function mapOneToOne(IEntity $entity, OneToOne $annotation, string $property, $value): void
    {
        if ($value instanceof IEntity) {
            $entity->_setEntityProperty($property, $value);
            $this->entityManager->persist($value);

            return;
        }

        $entity->_setEntityProperty(
            $property,
            $this->entityManager->getRepository($annotation->targetEntity)->find($value)
        );
    }

    /**
     * @param IEntity $entity
     * @param OneToMany $annotation
     * @param string $property
     * @param $value
     */
    private function mapOneToMany(IEntity $entity, OneToMany $annotation, string $property, $value): void
    {
        $entity->_setEntityProperty(
            $property,
            $this->entityManager->getRepository($annotation->targetEntity)->findBy(['id' => $value])
        );
    }

    /**
     * @param IEntity $entity
     * @param ManyToOne $annotation
     * @param string $property
     * @param $value
     */
    private function mapManyToOne(IEntity $entity, ManyToOne $annotation, string $property, $value): void
    {
        if ($value instanceof IEntity) {
            $entity->_setEntityProperty($property, $value);
            $this->entityManager->persist($value);

            return;
        }

        if ($value instanceof FileUpload) {
            if (trim($annotation->targetEntity, '\\') === File::class) {
                if ($oldFile = $entity->_getEntityProperty($property)) {
                    $this->entityManager->remove($oldFile);
                }

                if ($value->hasFile()) {
                    $file = new File();
                    $file->getStorage()->store(DataMedium::fromFile($value));
                    $this->entityManager->persist($file);
                } else {
                    $file = null;
                }

                $entity->_setEntityProperty($property, $file);

                return;
            }

            if (trim($annotation->targetEntity, '\\') === Image::class) {
                if ($oldImage = $entity->_getEntityProperty($property)) {
                    $this->entityManager->remove($oldImage);
                }

                if ($value->hasFile()) {
                    $image = new Image();
                    $image->getStorage()->store(DataMedium::fromFile($value));
                    $this->entityManager->persist($image);
                } else {
                    $image = null;
                }

                $entity->_setEntityProperty($property, $image);

                return;
            }
        }

        $entity->_setEntityProperty(
            $property,
            $this->entityManager->getRepository($annotation->targetEntity)->find($value)
        );
    }

    /**
     * @param IEntity $mappingEntity
     * @param ManyToMany $annotation
     * @param string $property
     * @param array $values
     * @return void
     */
    private function mapManyToMany(IEntity $mappingEntity, ManyToMany $annotation, string $property, array $values): void
    {
        $isInverse = (bool) $annotation->mappedBy;

        /** @var Collection $collection */
        $collection = $mappingEntity->_getEntityProperty($property);

        /** @var Collection $entities */
        $entities = $this->entityManager->getRepository($annotation->targetEntity)->findBy(['id' => $values]);

        $collection->map(static function (IEntity $entity) use ($mappingEntity, $annotation, $collection, $entities, $isInverse) {
            if (! $entities->contains($entity)) {
                $collection->removeElement($entity);

                if ($isInverse) {
                    /** @var Collection $foreignCollection */
                    $inverseCollection = $entity->_getEntityProperty($annotation->mappedBy);
                    $inverseCollection->removeElement($mappingEntity);
                }
            }
        });

        $entities->map(static function (IEntity $entity) use ($mappingEntity, $annotation, $collection, $isInverse) {
            if (! $collection->contains($entity)) {
                $collection->add($entity);

                if ($isInverse) {
                    /** @var Collection $foreignCollection */
                    $inverseCollection = $entity->_getEntityProperty($annotation->mappedBy);
                    $inverseCollection->add($mappingEntity);
                }
            }
        });
    }

    /**
     * @param $entity
     * @param string $encodedCode
     */
    private function processPostSave($entity, string $encodedCode): void
    {
        try {
            $code = CryptoHelper::decode($encodedCode, '', true);
            $fx = static function (IEntity $entity, EntityManager $entityManager) use ($code) {
                eval($code);
            };
            $fx($entity, $this->entityManager);
        } catch (NotFoundException) {
        } catch (Exception $e) {
            Debugger::log($e);
        }
    }
}
