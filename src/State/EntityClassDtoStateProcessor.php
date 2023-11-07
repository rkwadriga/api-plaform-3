<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserApi;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class EntityClassDtoStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire(service: PersistProcessor::class)]
        private readonly ProcessorInterface $persistProcessor,
        #[Autowire(service: RemoveProcessor::class)]
        private readonly ProcessorInterface $removeProcessor
    ) {
    }

    /**
     * @param UserApi $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return UserApi|void
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $entity = $this->mapDtoToEntity($data);

        if ($operation instanceof DeleteOperationInterface) {
            $this->removeProcessor->process($entity, $operation, $uriVariables, $context);

            return;
        }

        $this->persistProcessor->process($entity, $operation, $uriVariables, $context);
        $data->id = $entity->getId();

        return $data;
    }

    private function mapDtoToEntity(UserApi $data): User
    {
        if ($data->id === null) {
            $entity = new User();
        } else {
            $entity = $this->userRepository->find($data->id);
            if ($entity === null) {
                throw new NotFoundHttpException("Entity #{$data->id} not found");
            }
        }

        $entity
            ->setEmail($data->email)
            ->setUsername($data->username)
        ;

        if ($data->password !== null) {
            $entity->setPassword($this->passwordHasher->hashPassword($entity, $data->password));
        }

        return $entity;
    }
}
