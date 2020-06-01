<?php

namespace AppBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\JsonLd\ContextBuilderInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Serializer\ContextTrait;


class DocumentItemNormalizer implements NormalizerInterface
{
    use \ApiPlatform\Core\Serializer\ContextTrait;
    use \ApiPlatform\Core\JsonLd\Serializer\JsonLdContextTrait;

    const FORMAT = 'jsonld';

    private $iriConverter;
    private $resourceClassResolver;
    private $resourceMetadataFactory;
    private $contextBuilder;

    public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory, IriConverterInterface $iriConverter, ResourceClassResolverInterface $resourceClassResolver, ContextBuilderInterface $contextBuilder)
    {
        $this->iriConverter = $iriConverter;
        $this->resourceClassResolver = $resourceClassResolver;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->contextBuilder = $contextBuilder;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $relatedDocuments = [];
        $resourceClass = $this->resourceClassResolver->getResourceClass($object, $context['resource_class'] ?? null, true);
        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);

        $context = $this->initContext($resourceClass, $context);
        $context['api_normalize'] = true;

        $data = $this->addJsonLdContext($this->contextBuilder, $resourceClass, $context);

        $result = $object->getContent();

        $data['@id'] = $object->getId();

        $data['@type'] =  $result['@contentType'][0] ?? $result['@type'][0] ?? $resourceMetadata->getIri() ?? $resourceMetadata->getShortName();

        if (isset($object->getContent()['fullRelatedResources'])) {
            foreach ($object->getContent()['fullRelatedResources'] as $doc)
                $relatedDocuments [] = gettype($doc) == "object" ? $this->normalize($doc) : $doc;
            $result['fullRelatedResources'] = $relatedDocuments;
        }

        return $data + $result;
    }

    public function supportsNormalization($data, $format = null)
    {
        if (self::FORMAT !== $format || !is_object($data)) {
            return false;
        }

        try {
            $class = $this->resourceClassResolver->getResourceClass($data);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return $class === 'AppBundle\Entity\Document';
    }
}
