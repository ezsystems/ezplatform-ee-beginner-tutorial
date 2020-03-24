<?php

namespace AppBundle\Block\Event\Listener;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Renderer\BlockRenderEvents;
use EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Renderer\Event\PreRenderEvent;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RandomBlockListener implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;
    /**
     * @var LocationService
     */
    private $locationService;
    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * BannerBlockListener constructor.
     *
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     */
    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        SearchService $searchService
    ) {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->searchService = $searchService;
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            BlockRenderEvents::getBlockPreRenderEventName('random') => 'onBlockPreRender',
        ];
    }

    /**
     * @param \EzSystems\EzPlatformPageFieldType\FieldType\Page\Block\Renderer\Event\PreRenderEvent $event
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onBlockPreRender(PreRenderEvent $event)
    {
        //BlockDefinitionFactory
        $blockValue = $event->getBlockValue();
        $renderRequest = $event->getRenderRequest();
        $contentInfo = $this->contentService->loadContentInfo($blockValue->getAttribute('parentContentId')->getValue());

        $randomContent = $this->getRandomContent(
            $this->getQuery($contentInfo->mainLocationId)
        );

        $parameters = $renderRequest->getParameters();
        $parameters['content'] = $randomContent;

        $renderRequest->setParameters($parameters);
    }

    /**
     * Returns random picked Content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
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

}