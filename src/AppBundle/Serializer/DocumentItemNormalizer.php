<?php

namespace AppBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\JsonLd\ContextBuilderInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Serializer\ContextTrait;
use ML\JsonLD\JsonLD;


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
        
        if (file_exists(__DIR__ . '/response.jsonld')) {
            unlink (__DIR__ . '/response.jsonld');
        }
        // if (file_exists(__DIR__ . '/log.txt')) {
        //     unlink (__DIR__ . '/log.txt');
        // }
        $line = json_encode($data + $result, JSON_UNESCAPED_SLASHES);

        // file_put_contents( __DIR__ . '/response.jsonld', $data + $result, FILE_APPEND);
        file_put_contents( __DIR__ . '/response.jsonld', JsonLD::toString($line). PHP_EOL, FILE_APPEND);

        $config = json_decode(file_get_contents(__DIR__ . '/context-config.jsonld'));
        $jsonld = json_decode(file_get_contents(__DIR__ . '/response.jsonld'));

        if (isset($data['@context'])) {
            return $data + $result;
        }

        $test = JsonLD::frame($jsonld, $config);
        
        // $log = date('Y-m-d H:i:s') . ' ' . print_r($data, true);
        // file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);
        return $test;
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
