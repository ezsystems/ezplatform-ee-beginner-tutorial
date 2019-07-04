<?php

namespace App\Controller;

use Symfony\Component\Templating\EngineInterface;
use eZ\Publish\API\Repository\SearchService;
use App\QueryType\MenuQueryType;

class MenuController
{
    /** @var \Symfony\Bundle\TwigBundle\TwigEngine */
    protected $templating;
    /** @var \eZ\Publish\API\Repository\SearchService */
    protected $searchService;
    /** @var \App\QueryType\MenuQueryType */
    protected $menuQueryType;

    /**
     * @param \Symfony\Bundle\TwigBundle\TwigEngine $templating
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \App\QueryType\MenuQueryType $menuQueryType
     */
    public function __construct(
        EngineInterface $templating,
        SearchService $searchService,
        MenuQueryType $menuQueryType
    )
    {
        $this->templating = $templating;
        $this->searchService = $searchService;
        $this->menuQueryType = $menuQueryType;
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