<?php

namespace App\Controller;

use Twig\Environment;
use eZ\Publish\API\Repository\SearchService;
use App\QueryType\MenuQueryType;
use Symfony\Component\HttpFoundation\Response;

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
        Environment $templating,
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
        $content = $this->templating->render(
            $template, [
                'menuItems' => $menuItems,
            ]
        );
        $response = new Response();
        $response->setContent($content);
        
        return $response;
    }
}
