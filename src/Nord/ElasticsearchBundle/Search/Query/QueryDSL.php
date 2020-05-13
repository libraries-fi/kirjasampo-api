<?php namespace Nord\ElasticsearchBundle\Search\Query;

/**
 * Elasticsearch provides a full Query DSL based on JSON-LD to define queries.
 * Think of the Query DSL as an AST of queries, consisting of two types of clauses:
 *
 * - "Leaf query clauses"
 * Leaf query clauses look for a particular value in a particular field, such as the match, term or range queries.
 * These queries can be used by themselves.
 *
 * - "Compound query clauses"
 * Compound query clauses wrap other leaf or compound queries and are used to combine multiple queries in a logical
 * fashion (such as the bool or dis_max query), or to alter their behaviour (such as the not or constant_score query).
 *
 * Query clauses behave differently depending on whether they are used in query context or filter context.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl.html
 */
abstract class QueryDSL
{
    /**
     * @return array
     */
    abstract public function toArray();
}
