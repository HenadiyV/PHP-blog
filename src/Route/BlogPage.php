<?php

namespace App\Route;

use App\PostMapper;
use App\Session;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class BlogPage
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

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param $args
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response,$args): ResponseInterface {

        $page = isset($args['page']) ? (int) $args['page']:1;
        $limit = 2;
        try{
            $posts = $this->postMapper->getList($page,$limit,'DESC');
            $totalCount = $this->postMapper->getTotalCount();
            $body = $this->twig->render('blog.twig',[
                'user'=>$this->session->getData('user'),
                'posts'=>$posts,
                'pagination' => [
                    'current' => $page,
                    'paging' => ceil($totalCount / $limit)
                ]
            ]);
            $response->getBody()->write($body);
            return $response;
        }catch(Exception $e){
            return $response->withHeader('Location','/blog')->withStatus(302);
        }

    }
}