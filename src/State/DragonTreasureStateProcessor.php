<?php declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DragonTreasure;
use App\Entity\User;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

//#[Symfony\Component\DependencyInjection\Attribute\AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class DragonTreasureStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $innerProcessor,
        private readonly Security $security
    ) {
    }

    /**
     * @param DragonTreasure $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return void
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($data->getOwner() === null) {
            /** @var User $user */
            $user = $this->security->getUser();
            if ($user === null) {
                throw new LogicException('DragonTreasureSetOwnerProcessor should only be used when a user is logged in');
            }

            $data->setOwner($user);
        }

        $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        $user = $this->security->getUser();
        $data->setIsOwnedByAuthenticatedUser($user !== null && $user === $data->getOwner());
    }
}
