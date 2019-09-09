<?php

namespace SahaBundle\LinkedData;

use EasyRdf_Graph;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Length;

class LinksBuilder
{
    protected $config;
    protected $output;
    protected $links = [];
    protected $linkedData = [];
    protected $sourceFile;

    public function __construct(InputInterface $sourceFile, OutputInterface $output)
    {
        $fileNumber = 0;
        $this->output = $output;
        $this->sourceFile = $sourceFile->getArgument('file');
        $this->links = [];
        $this->config = include "config.php";

        $availableProps = [];
        array_walk_recursive($this->config, function($prop) use (&$availableProps) { if ($prop != 'inverse') $availableProps[] = $prop; });

        $this->output->writeln('<info>Start scan files for build links map</info>');

        while (file_exists($this->sourceFile . str_pad($fileNumber, 4, '0', STR_PAD_LEFT))) {
            $fileSuffix = str_pad($fileNumber, 4, '0', STR_PAD_LEFT);

            $data = new EasyRdf_Graph();
            $data->parseFile($this->sourceFile . $fileSuffix, 'ntriples');

            $resources = json_decode($data->serialise('jsonld'), true);

            foreach ($resources as $resource) {
                if (isset($resource['@id'])) {
                    $res = new Resource($resource['@id'], $this->config, $this->output, $this->links, $availableProps);
                    $res->fill($resource);
                }
            }

            $this->output->writeln('<info>File ' . $fileNumber . ' was scanned</info>');
            $fileNumber++;
        }
    }

    public function getLinkedData($_resourceId)
    {
        return $this->links[$_resourceId]->getLinkedData();
    }

}

class Resource
{
    protected $id;
    protected $links;
    protected $config;
    protected $output;
    protected $props = [];
    protected $propsAll = [];
    protected $linkedData = [];
    protected $inverseProps = [];
    protected $availableProps = [];

    public function __construct($_resourceId, $_config, $_output, &$_links, $availableProps)
    {
        $this->id = $_resourceId;
        $this->config = $_config;
        $this->output = $_output;
        $this->links = &$_links;
        $this->availableProps = $availableProps;

        if (isset($this->links[$_resourceId])) {
            return $this->links[$_resourceId];
        } else {
            $this->links[$_resourceId] = $this;
            return $this;
        }
    }

    public function fill($_resource)
    {
        foreach ($_resource as $property => $resources)
            if (in_array($property, $this->availableProps))
                $this->addProperty($property, $resources);
    }

    private function addProperty($property, $_resources)
    {
        foreach ($_resources as $types) {
            foreach ($types as $type => $resource) {
                if ($type == '@id') {
                    $this->props[$property][] = $resource;

                    $res = new Resource($resource, $this->config, $this->output, $this->links, $this->availableProps);
                    $res->addInverseProperty($property, $this->id);
                }
            }
        }
    }

    public function addInverseProperty($_property, $_parentResource)
    {
        $this->inverseProps[$_property][] = $_parentResource;
    }

    private function flattenResults($arr){
        $result = [];
        array_walk_recursive($arr, function($resourceId) use (&$result) { $result[] = $resourceId; });
        $result = array_unique($result);

        return $result;
    }

    public function getLinkedData($_path = null)
    {
        $result = [];
        $path = isset($_path['path']) ? $_path['path'] : [];
        $inverse = isset($_path['inverse']);
        $all = isset($_path['all']) ? $_path['all'] : false;
        $config = $_path ? $_path : $this->config;
        $properties = $inverse ? $this->inverseProps : $this->props;

        unset($_path['path']);
        unset($_path['inverse']);
        unset($_path['all']);

        if ($all) {
            $result[] = $properties;
        }

        if (!$_path) {
            foreach ($config as $configProperty)
                $result [] = $this->getLinkedData($configProperty);

            $result = $this->flattenResults($result);
            return $result;
        }

        foreach ($_path as $property) {
            if (isset($properties[$property])) {

                $result [] = $properties[$property];

                if ($path) {
                    foreach ($properties[$property] as $resourceId) {
                        $result [] = $this->links[$resourceId]->getLinkedData($path);
                    }
                }
            }
        }

        $result = $this->flattenResults($result);
        return $result;
    }
}