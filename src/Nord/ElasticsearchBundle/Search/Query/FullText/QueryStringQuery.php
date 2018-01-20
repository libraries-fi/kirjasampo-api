<?php namespace Nord\ElasticsearchBundle\Search\Query\FullText;

use Nord\ElasticsearchBundle\Exceptions\InvalidArgument;

/**
 * A query that uses a query parser in order to parse its content.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html
 */
class QueryStringQuery extends AbstractQuery
{
    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $default_field;

    /**
     * @var string
     */
    private $default_operator;

    /**
     * Defau
     * @var array
     */
    private $fields;

    /**
     * @var float A boost can be specified to give this query string query a higher relevance score than another query.
     */
    private $boost;

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $queryString = ['query' => $this->getQuery()];

        $boost = $this->getBoost();
        if (!is_null($boost)) {
            $queryString['boost'] = $boost;
        }

        $defaultField = $this->getDefaultField();
        if (!is_null($defaultField)) {
            $queryString['default_field'] = $defaultField;
        }

        $defaultOperator = $this->getDefaultOperator();
        if (!is_null($defaultOperator)) {
            $queryString['default_operator'] = $defaultOperator;
        }

        $fields = $this->getFields();
        if (!is_null($fields)) {
            $queryString['fields'] = $fields;
        }

        return ['query_string' => $queryString];
    }

    /**
     * @param string $query
     * @return QueryStringQuery
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $field
     * @return QueryStringQuery
     */
    public function setDefaultField($field)
    {
        $this->default_field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultField()
    {
        return $this->default_field;
    }

    /**
     * @param string $defaultOperator
     * @return QueryStringQuery
     */
    public function setDefaultOperator($defaultOperator)
    {
        $this->default_operator = $defaultOperator;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultOperator()
    {
        return $this->default_operator;
    }

    /**
     * @param array $fields
     * @return QueryStringQuery
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param float $boost
     * @return QueryStringQuery
     * @throws InvalidArgument
     */
    public function setBoost($boost)
    {
        $this->assertBoost($boost);
        $this->boost = $boost;
        return $this;
    }

    /**
     * @return float
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * @param float $boost
     * @throws InvalidArgument
     */
    protected function assertBoost($boost)
    {
        if (!is_float($boost)) {
            throw new InvalidArgument(sprintf(
                'Query String Query `boost` must be a float value, "%s" given.',
                gettype($boost)
            ));
        }
    }
}
