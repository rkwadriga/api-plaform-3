<?php declare(strict_types=1);
/**
 * Created 2023-10-27 18:10:14
 * Author rkwadriga
 */

namespace App\Normalizer;

use App\Entity\DragonTreasure;
use ArrayObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsDecorator('api_platform.jsonld.normalizer.item')]
class AddOwnerGroupsNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    public function __construct(
        private readonly NormalizerInterface $decorated,
        private readonly Security $security
    ) {
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array|ArrayObject|bool|float|int|null|string
    {
        if (isset($context['groups']) && $object instanceof DragonTreasure && $object->getOwner() === $this->security->getUser()) {
            $context['groups'][] = 'owner:read';
        }

        return $this->decorated->normalize($object, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->decorated->getSupportedTypes($format);
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}