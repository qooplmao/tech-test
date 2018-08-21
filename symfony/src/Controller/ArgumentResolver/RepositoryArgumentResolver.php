<?php declare(strict_types=1);

namespace App\Controller\ArgumentResolver;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class RepositoryArgumentResolver implements ArgumentValueResolverInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var array|string[]
     */
    private $repositoryClassMap;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;

        /** @var array|ClassMetadata[] $metadataForAllRegisteredEntities */
        $metadataForAllRegisteredEntities = $managerRegistry->getManager()->getMetadataFactory()->getAllMetadata();

        foreach ($metadataForAllRegisteredEntities as $metadata) {
            $className = $metadata->getName();
            $repository = $this->managerRegistry->getManagerForClass($className)->getRepository($className);

            $this->repositoryClassMap[get_class($repository)] = $className;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return array_key_exists($argument->getType(), $this->repositoryClassMap);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if (!$this->supports($request, $argument)) {
            yield null;
        }

        $className = $this->repositoryClassMap[$argument->getType()];

        yield $this->managerRegistry->getManagerForClass($className)->getRepository($className);
    }
}