<?php

namespace SahaBundle\RelatedResources;

use EasyRdf_Graph;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RelatedResourcesBuilder
{
    protected $docs;
    protected $prefixes;
    protected $definitions;

    protected $topResources = [];
    protected $deepResources = [];
    protected $properties = [];

    protected $relatedResources;

    protected $output;
    protected $sourceFile;

    public function __construct(InputInterface $sourceFile, OutputInterface $output)
    {
        $json = file_get_contents('configuration.json', FILE_USE_INCLUDE_PATH);
        $json_data = json_decode($json, true);

        $prefixes = $json_data["prefixes"];
        $definitions = $json_data["definitions"];

        $this->output = $output;
        $this->sourceFile = $sourceFile->getArgument('file');

        $this->convertPrefixes($prefixes);
        $this->convertDefinitions($definitions);
        $this->scanFilesForDeepRelatedResources();
    }

    private function scanFilesForDeepRelatedResources()
    {
        $fileNumber = 0;
        $topResources = $this->topResources;
        $deepResources = $this->deepResources;
        $properties = $this->properties;

        while (file_exists($this->sourceFile . str_pad($fileNumber, 4, '0', STR_PAD_LEFT))) {
            $fileSuffix = str_pad($fileNumber, 4, '0', STR_PAD_LEFT);

            $data = new EasyRdf_Graph();
            $data->parseFile($this->sourceFile . $fileSuffix, 'ntriples');

            $data = json_decode($data->serialise('jsonld'), true);

            foreach ($data as $graph) {
                if (isset($graph['@id']))
                    foreach ($graph as $type => $resources)
                        if ($type != '@id' && $type != '@value') {
                            if (in_array($type, $topResources))
                                $this->addTopResource($graph["@id"], $resources);

                            if (in_array($type, $deepResources))
                                $this->addDocumentWithDeepResource($graph["@id"], $type, $resources);
                        }
            }
            $fileNumber++;
        }

        $types = array_keys(array_filter($properties, function ($var) {
            return (!isset($var["parent"]));
        }));

        foreach ($types as $type) {
            $docs = array_filter($this->docs, function ($doc) use ($type) {
                return (array_key_exists($type, $doc));
            });

            foreach ($docs as $id => $doc) {
                $this->getDeepResource($doc[$type], $type, $id);
            }
        };
    }

    private function addTopResource($id, $properties)
    {
        foreach ($properties as $property)
            $this->relatedResources[$id][] = $property;
    }

    private function addDocumentWithDeepResource($id, $containsType, $properties)
    {
        foreach ($properties as $property) {
            foreach ($property as $type => $value) {
                if ($type == "@id")
                    $this->docs[$id][$containsType]["ids"][] = $value;
                $this->docs[$id][$containsType]["properties"][] = [$type => $value];
            }
        }
    }

    private function getDeepResource($doc, $type, $root)
    {
        $properties = $this->properties;

        if (isset($properties[$type]["child"]) && isset($doc["ids"])) {
            foreach ($doc["ids"] as $id){

                //$this->relatedResources[$root][] = ["@id" => $id];

                foreach ($properties[$type]["child"] as $childType)
                    if (isset($this->docs[$id][$childType]))
                        $this->getDeepResource($this->docs[$id][$childType], $childType, $root);
            }
        }
        foreach ($doc["properties"] as $property)
            $this->relatedResources[$root][] = $property;
    }

    private function convertPrefixes($prefixes)
    {
        foreach ($prefixes as $prefix) {
            $prefixParts = explode(":", $prefix, 2);
            $this->prefixes[$prefixParts[0]] = $prefixParts[1];
        }
    }

    private function convertDefinitions(array $definitions, $parent = null)
    {
        $topResources = &$this->topResources;
        $deepResources = &$this->deepResources;
        $properties = &$this->properties;

        foreach ($definitions as $definition) {
            if (is_string($definition["property"]))
                $definition["property"] = [$definition["property"]];

            foreach ($definition["property"] as $property) {
                $property = explode(":", $property, 2);
                $prefixInsertion = $this->prefixes[$property[0]];
                $value = $prefixInsertion . $property[1];

                if (!$parent
                    && !isset($definition["path"])
                    && !in_array($value, $topResources)
                )
                    $topResources[] = $value;

                if ($parent
                    && (!isset($properties[$parent]["child"])
                        || !in_array($value, $properties[$parent]["child"])
                    )
                ) {
                    $properties[$value]["parent"][] = $parent;
                    $properties[$parent]["child"][] = $value;
                }

                if (isset($definition["path"]))
                    $this->convertDefinitions([$definition["path"]], $value);
            }
        }

        if (!$parent) {
            $deepResources = array_keys($properties);
        }
    }

    public function getRelatedResources()
    {
        return $this->relatedResources;
    }
}