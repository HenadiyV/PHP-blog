<?php

namespace App\Route;

use App\Database;
use App\LatestPost;
use App\Session;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class HomePage
{
        /**
         * @var LatestPost
         */
    private LatestPost $latestPost;
        /**
         * @var Environment
         */
    private Environment $twig;
        /**
         * @var Session
         */
    private Session $session;

        /**
         * @param LatestPost $latestPost
         * @param Environment $twig
         * @param Session $session
         */
    public function __construct(LatestPost $latestPost,Environment $twig,Session $session){
        $this->latestPost = $latestPost;
        $this->twig = $twig;
        $this->session = $session;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function execute(Request $request,Response $response): Response{
    $posts = $this->latestPost->get(3);
    $body = $this->twig->render('index.twig',[
        'user'=>$this->session->getData('user'),
        'posts'=>$posts
    ]);
    $response->getBody()->write($body);
    return $response;
}
}