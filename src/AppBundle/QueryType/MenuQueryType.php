<?php

namespace AppBundle\QueryType;

use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

class MenuQueryType implements QueryType
{
    public function getQuery(array $parameters = [])
    {
        $criteria = new Query\Criterion\LogicalAnd([
            new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE),
            new Query\Criterion\Location\Depth(Query\Criterion\Operator::LTE, 2),
            new Query\Criterion\Subtree('/1/2/'),
        ]);

        $options = [
            'filter' => $criteria,
            'sortClauses' => [
                new SortClause\Location\Priority(LocationQuery::SORT_ASC),
            ],
        ];

        return new LocationQuery($options);
    }

    public static function getName()
    {
        return 'AppBundle:Menu';
    }

    public function getSupportedParameters()
    {
        return [];
    }
}
