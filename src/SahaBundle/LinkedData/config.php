<?php
return [
    //_:d1
    //rdf:type d:RDFPathDescribeHandlerDescription ;
    //d:targetType http://www.yso.fi/onto/kaunokki#teos ;
    //d:context "browse" ;
    //d:path
    //_:fyysinenteos,
    [
        'http://www.yso.fi/onto/kaunokki#ketjutettu_asiasana'
    ],
    [
        'http://www.yso.fi/onto/kaunokki#worldPlace'
    ],
    [
        'http://kulttuurisampo.fi/annotaatio#sivuUrl'
    ],
    [
        'http://www.yso.fi/onto/kaunokki#hasReview'
    ],
    [
        'http://www.yso.fi/onto/kaunokki#eSampo'
    ],
    [
        'http://www.yso.fi/onto/kaunokki#tekija'
    ],

    //_:d2
    //rdf:type d:RDFPathDescribeHandlerDescription ;
    //d:targetType http://www.yso.fi/onto/kaunokki#teos ;
    //d:context "search" ;
    //d:path
    [
        'http://www.yso.fi/onto/kaunokki#manifests_in',
        'http://www.yso.fi/onto/kaunokki#manifests_in_part',
        'path' => [
            'http://www.yso.fi/onto/kaunokki#kansikuva',
            'path' => [
                'http://kulttuurisampo.fi/annotaatio#tiedostoUrl'
            ]
        ]
    ],
    [
        'http://www.yso.fi/onto/kaunokki#ilmestymisvuosi',
    ],
    [
        'http://www.yso.fi/onto/kaunokki#tekija'
    ],

    //_:d3
    //rdf:type d:RDFPathDescribeHandlerDescription ;
    //d:targetType http://www.yso.fi/onto/kaunokki#fyysinen_teos ;
    [
        'inverse' => true,
        'http://www.yso.fi/onto/kaunokki#manifests_in',
        'http://www.yso.fi/onto/kaunokki#manifests_in_part'
    ],

    //:d4
    //rdf:type d:RDFPathDescribeHandlerDescription ;
    //d:targetType foaf:Person ;
    //d:path
    [
        'http://seco.tkk.fi/saha3/kirjasampo/kirjailijanKuva'
    ],

    [
        'http://kulttuurisampo.fi/annotaatio#sivuUrl'
    ],

    //_:recommendations
    //d:property http://www.yso.fi/onto/kaunokki#suosittelu ;
    [
        'http://www.yso.fi/onto/kaunokki#manifests_in',
        'http://www.yso.fi/onto/kaunokki#manifests_in_part',
        'path' => [
            'http://www.yso.fi/onto/kaunokki#kansikuva'
        ]
    ],

    //_:parts
    //d:inverseProperty http://www.yso.fi/onto/kaunokki#partOfCollectiveWorks ;
    [
        'inverse' => true,
        'http://www.yso.fi/onto/kaunokki#manifests_in',
        'http://www.yso.fi/onto/kaunokki#manifests_in_part'
    ],

    //_:sovitukset
    //d:property http://www.yso.fi/onto/kaunokki#sovitukset, http://www.yso.fi/onto/kaunokki#hasFilmVersion ;
    [
        'http://kulttuurisampo.fi/annotaatio#sivuUrl'
    ],

    //_:fyysinenteos
    //d:property http://www.yso.fpartOfCollectiveWorksi/onto/kaunokki#manifests_in , http://www.yso.fi/onto/kaunokki#manifests_in_part ;
    //d:path
    [
        'http://www.yso.fi/onto/kaunokki#kansikuva',
        'http://www.yso.fi/onto/kaunokki#kaantaja'
    ],

    //_:sarjamerkinta
    //d:property http://www.yso.fi/onto/kaunokki#sarjassa ;
    [
        'http://www.yso.fi/onto/kaunokki#sarjaInstanssi'
    ],

    //_:palkinto
    //d:property http://www.yso.fi/onto/kaunokki#onPalkinto ;
    [
        'http://www.yso.fi/onto/kaunokki#palkintosarja'
    ],

    //_:d5
    //rdf:type d:RDFPathDescribeHandlerDescription ;
    //d:targetType http://www.yso.fi/onto/kaunokki#palkinto ;
    [
        'inverse' => true,
        'http://www.yso.fi/onto/kaunokki#palkintosarja',
        'path' => [
            'inverse' => true,
            'http://www.yso.fi/onto/kaunokki#onPalkinto',
            'http://www.yso.fi/onto/kaunokki#hasAward'
        ]
    ]
];
