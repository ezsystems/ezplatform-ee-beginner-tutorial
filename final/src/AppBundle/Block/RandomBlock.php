<?php

namespace AppBundle\Block;
use EzSystems\LandingPageFieldTypeBundle\Exception\InvalidBlockAttributeException;
use EzSystems\LandingPageFieldTypeBundle\FieldType\LandingPage\Definition\BlockDefinition;
use EzSystems\LandingPageFieldTypeBundle\FieldType\LandingPage\Definition\BlockAttributeDefinition;
use EzSystems\LandingPageFieldTypeBundle\FieldType\LandingPage\Model\AbstractBlockType;
use EzSystems\LandingPageFieldTypeBundle\FieldType\LandingPage\Model\BlockValue;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;

class RandomBlock extends AbstractBlockType
{
    /**
     * Content ID regular expression.
     *
     * @example 16
     *
     * @var string
     */
    const PATTERN_CONTENT_ID = '/[0-9]+/';

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /**
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     */
    public function __construct(
        LocationService $locationService,
        ContentService $contentService,
        SearchService $searchService
    )
    {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->searchService = $searchService;
    }

    public function getTemplateParameters(BlockValue $blockValue)
    {
        $attributes = $blockValue->getAttributes();
        $contentInfo = $this->contentService->loadContentInfo($attributes['parentContentId']);
        $randomContent = $this->getRandomContent(
            $this->getQuery($contentInfo->mainLocationId)
        );
        return [
            'content' => $randomContent
        ];
    }

    /**
     * Returns random picked Content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    private function getRandomContent(LocationQuery $query)
    {
        $results = $this->searchService->findLocations($query);
        $searchHits = $results->searchHits;
        if (count($searchHits) > 0) {
            shuffle($searchHits);
            return $this->contentService->loadContentByContentInfo(
                $searchHits[0]->valueObject->contentInfo
            );
        }
        return null;
    }

    /**
     * Returns LocationQuery object based on given arguments.
     *
     * @param int $parentLocationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationQuery
     */
    private function getQuery($parentLocationId)
    {
        $query = new LocationQuery();
        $query->query = new Criterion\LogicalAnd([
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\ParentLocationId($parentLocationId),
        ]);
        return $query;
    }

    public function createBlockDefinition()
    {
        return new BlockDefinition(
            'random',
            'Random',
            'default',
            'assets/images/blocks/random_block.svg',
            [],
            [
                new BlockAttributeDefinition(
                    'parentContentId',
                    'Parent',
                    'embed',
                    self::PATTERN_CONTENT_ID,
                    'Choose a valid ContentID',
                    true,
                    false,
                    [],
                    []
                ),
            ]
        );
    }

    public function checkAttributesStructure(array $attributes)
    {
        if (!isset($attributes['parentContentId']) || preg_match(self::PATTERN_CONTENT_ID, $attributes['parentContentId']) !== 1) {
            throw new InvalidBlockAttributeException('Parent container', 'parentContentId', 'Parent ContentID must be defined.');
        }
    }
}
