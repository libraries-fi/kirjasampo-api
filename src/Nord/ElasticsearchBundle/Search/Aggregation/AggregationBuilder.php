<?php namespace Nord\ElasticsearchBundle\Search\Aggregation;

use Nord\ElasticsearchBundle\Search\Aggregation\Bucket\GlobalAggregation;
use Nord\ElasticsearchBundle\Search\Aggregation\Metrics\MaxAggregation;
use Nord\ElasticsearchBundle\Search\Aggregation\Metrics\MinAggregation;

class AggregationBuilder
{
    /**
     * @return GlobalAggregation
     */
    public function createGlobalAggregation()
    {
        return new GlobalAggregation();
    }


    /**
     * @return MaxAggregation
     */
    public function createMaxAggregation()
    {
        return new MaxAggregation();
    }


    /**
     * @return MinAggregation
     */
    public function createMinAggregation()
    {
        return new MinAggregation();
    }
}
