<?php

namespace App\Route;

use App\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class AboutPage
{
    private Environment $twig;
    private Session $session;

    public function __construct(Environment $twig,Session $session)
    {
        $this->twig = $twig;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request,ResponseInterface $response): ResponseInterface {

    $body = $this->twig->render('about.twig',[
        'user'=>$this->session->getData('user'),
    ]);
    $response->getBody()->write($body);
    return $response;
}
}