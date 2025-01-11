<?php

namespace App\Route;

use App\PostMapper;
use App\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class PostRout
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
     * @var PostMapper
     */
    private PostMapper $postMapper;


    /**
     * @param Environment $twig
     * @param Session $session
     * @param PostMapper $postMapper
     */
    public function __construct(Environment $twig,Session $session,PostMapper $postMapper)
    {
        $this->twig = $twig;
        $this->session = $session;
        $this->postMapper = $postMapper;
    }

    public function __invoke(ServerRequestInterface $request,ResponseInterface $responce, array $args=[]): ResponseInterface{

        $post = $this->postMapper->getByUrlKey((string) $args['url_key']);

        if(empty($post)){
            $body = $this->twig->render('not-found.twig');
        }else{
            $body = $this->twig->render('post.twig',[
                'url_key'=>$args['url_key'],
                'user'=>$this->session->getData('user'),
                'post'=>$post
            ]);
        }
        $responce->getBody()->write($body);
        return $responce;

    }
}