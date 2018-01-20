<?php namespace Nord\ElasticsearchBundle\Search\Query;

use Nord\ElasticsearchBundle\Search\Query\Compound\BoolQuery;
use Nord\ElasticsearchBundle\Search\Query\Compound\FunctionScoreQuery;
use Nord\ElasticsearchBundle\Search\Query\FullText\MatchQuery;
use Nord\ElasticsearchBundle\Search\Query\FullText\MultiMatchQuery;
use Nord\ElasticsearchBundle\Search\Query\FullText\QueryStringQuery;
use Nord\ElasticsearchBundle\Search\Query\Geo\GeoDistanceQuery;
use Nord\ElasticsearchBundle\Search\Query\Joining\HasChildQuery;
use Nord\ElasticsearchBundle\Search\Query\Joining\HasParentQuery;
use Nord\ElasticsearchBundle\Search\Query\Joining\NestedQuery;
use Nord\ElasticsearchBundle\Search\Query\TermLevel\ExistsQuery;
use Nord\ElasticsearchBundle\Search\Query\TermLevel\RangeQuery;
use Nord\ElasticsearchBundle\Search\Query\TermLevel\RegexpQuery;
use Nord\ElasticsearchBundle\Search\Query\TermLevel\TermQuery;
use Nord\ElasticsearchBundle\Search\Query\TermLevel\TermsQuery;
use Nord\ElasticsearchBundle\Search\Query\TermLevel\WildcardQuery;

class QueryBuilder
{
    /**
     * @return BoolQuery
     */
    public function createBoolQuery()
    {
        return new BoolQuery();
    }

    /**
     * @return FunctionScoreQuery
     */
    public function createFunctionScoreQuery()
    {
        return new FunctionScoreQuery();
    }

    /**
     * @return MatchQuery
     */
    public function createMatchQuery()
    {
        return new MatchQuery();
    }

    /**
     * @return MultiMatchQuery
     */
    public function createMultiMatchQuery()
    {
        return new MultiMatchQuery();
    }

    /**
     * @return QueryStringQuery
     */
    public function createQueryStringQuery()
    {
        return new QueryStringQuery();
    }

    /**
     * @return TermQuery
     */
    public function createTermQuery()
    {
        return new TermQuery();
    }

    /**
     * @return TermsQuery
     */
    public function createTermsQuery()
    {
        return new TermsQuery();
    }

    /**
     * @return WildcardQuery
     */
    public function createWildcardQuery()
    {
        return new WildcardQuery();
    }

    /**
     * @return RegexpQuery
     */
    public function createRegexpQuery()
    {
        return new RegexpQuery();
    }

    /**
     * @return RangeQuery
     */
    public function createRangeQuery()
    {
        return new RangeQuery();
    }

    /**
     * @return ExistsQuery
     */
    public function createExistsQuery()
    {
        return new ExistsQuery();
    }

    /**
     * @return GeoDistanceQuery
     */
    public function createGeoDistanceQuery()
    {
        return new GeoDistanceQuery();
    }

    /**
     * @return NestedQuery
     */
    public function createNestedQuery()
    {
        return new NestedQuery();
    }

    /**
     * @return HasChildQuery
     */
    public function createHasChildQuery()
    {
        return new HasChildQuery();
    }

    /**
     * @return HasParentQuery
     */
    public function createHasParentQuery()
    {
        return new HasParentQuery();
    }
}
