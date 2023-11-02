<?php declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DragonTreasure;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use LogicException;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class DragonTreasureSetOwnerProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $innerProcessor,
        private readonly Security $security
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($data instanceof DragonTreasure && $data->getOwner() === null) {
            /** @var User $user */
            $user = $this->security->getUser();
            if ($user === null) {
                throw new LogicException('DragonTreasureSetOwnerProcessor should only be used when a user is logged in');
            }

            $data->setOwner($user);
        }

        $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        if ($data instanceof DragonTreasure) {
            $user = $this->security->getUser();
            $data->setIsOwnedByAuthenticatedUser($user !== null && $user === $data->getOwner());
        }
    }
}
