<?php

namespace Evoweb\CurseDownloader\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ConfigIndex
 */
class ConfigIndex
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * ShowIndex constructor.
     *
     * @param RouterInterface $router
     * @param \Twig_Environment $twig
     */
    public function __construct(RouterInterface $router, \Twig_Environment $twig)
    {
        $this->router = $router;
        $this->twig = $twig;
    }

    public function __invoke(Request $request)
    {
        $body = $this->twig->render(':Templates/Page:Index.html.twig', [
        ]);

        return new Response($body, Response::HTTP_OK);
    }
}
