<?php

namespace AppBundle\Controller;

use Symfony\Bundle\TwigBundle\TwigEngine;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use eZ\Publish\API\Repository\SearchService;

class MenuController
{
    /** @var \Symfony\Bundle\TwigBundle\TwigEngine */
    protected $templating;

    /** @var \eZ\Publish\API\Repository\SearchService */
    protected $searchService;

    /** @var \AppBundle\QueryType\MenuQueryType */
    protected $menuQueryType;

    /**
     * @param \Symfony\Bundle\TwigBundle\TwigEngine $templating
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \eZ\Publish\Core\QueryType\QueryTypeRegistry $queryTypeRegistry
     */
    public function __construct(
        TwigEngine $templating,
        SearchService $searchService,
        QueryTypeRegistry $queryTypeRegistry
    ) {
        $this->templating = $templating;
        $this->searchService = $searchService;
        $this->menuQueryType = $queryTypeRegistry->getQueryType('AppBundle:Menu');
    }

    /**
     * Renders top menu with child items.
     *
     * @param string $template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getMenuItemsAction($template)
    {
        $locationSearchResults = $this->searchService->findLocations($this->menuQueryType->getQuery());

        $menuItems = [];
        foreach ($locationSearchResults->searchHits as $hit) {
            $menuItems[] = $hit->valueObject;
        }

        return $this->templating->renderResponse(
            $template, [
                'menuItems' => $menuItems,
            ]
        );
    }
}
