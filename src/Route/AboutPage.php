<?php

namespace App\Route;

use App\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class AboutPage
{
    /**
     * @var Environment
     */
    private Environment $twig;
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @param Environment $twig
     * @param Session $session
     */

    public function __construct(Environment $twig,Session $session)
    {
        $this->twig = $twig;
        $this->session = $session;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response): ResponseInterface {

    $body = $this->twig->render('about.twig',[
        'user'=>$this->session->getData('user'),
    ]);
    $response->getBody()->write($body);
    return $response;
}
}