<?php

namespace SahaBundle\Configuration;

class Configuration
{
    protected $relatedMap;
    protected $rulesConfig;
    protected $prefixesConfig;

    /**
     * @param null $rulesArray
     * @return array
     */
    public function getRelatedResourceMap($rulesArray = null): array
    {
        foreach ($rulesArray ?? $this->rulesConfig as $ruleElem) {
            if (!isset($ruleElem["path"])) {
                foreach ($ruleElem["property"] as $subRuleElem) {
                    $rule = explode(":", $subRuleElem);
                    $value = $this->prefixesConfig[$rule[0]] . $rule[1];
                    $this->relatedMap[] = $value;
                }
            }
        }

        return $this->relatedMap ?? [];
    }

    /**
     * @return string
     */
    public function getRelatedResourceRegexp(): string
    {
        foreach ($this->relatedMap as $id)
            $ids [] = preg_quote($id, "/");

        return $ids ?  '/' . implode("|", $ids) . '/i' : null;
    }

    /**
     * Configuration constructor.
     */
    public function __construct()
    {
        $prefixes = [
            "foaf:http://xmlns.com/foaf/0.1/",
            "ks:http://www.yso.fi/onto/kaunokki#",
            "ks2:http://seco.tkk.fi/saha3/kirjasampo/",
            "kulsa-schema:http://kulttuurisampo.fi/annotaatio#",
            "rdfs:http://www.w3.org/2000/01/rdf-schema#",
            "rdf:http://www.w3.org/1999/02/22-rdf-syntax-ns#",
            "d:http://purl.org/finnonto/schema/seco-query#",
        ];

        $this->rulesConfig = [
            ["property" => ["ks:eSampo"]],
            ["property" => ["ks:tekija"]],
            ["property" => ["ks:hasReview"]],
            ["property" => ["ks:worldPlace"]],
            ["property" => ["ks:ketjutettu_asiasana"]],

            [
                "property" => ["ks:manifests_in", "ks:manifests_in_part"],
                "path" => [
                    "property" => ["ks:kansikuva"],
                    "path" => [
                        "property" => ["kulsa-schema:tiedostoUrl"]
                    ]
                ]
            ],

            ["property" => ["ks:ilmestymisvuosi"]],
            ["property" => ["ks2:kirjailijanKuva"]],
            ["property" => ["kulsa-schema:sivuUrl"]],
            ["property" => ["ks:kaantaja"]],
            ["property" => ["ks:sarjaInstanssi"]],
            ["property" => ["ks:palkintosarja"]],
            ["property" => ["ks:onPalkinto"]],
        ];

        foreach ($prefixes as $prefix){
            $prefix = explode(":", $prefix, 2);
            $this->prefixesConfig[ $prefix[0] ] = $prefix[1];
        }

    }
}