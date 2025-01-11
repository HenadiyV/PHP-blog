<?php

namespace App\Route;

use App\Authorization;
use App\AuthorizationException;
use App\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class LoginPage
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
     * @var Authorization
     */
    private Authorization $authorithation;

    /**
     * @param Environment $twig
     * @param Session $session
     * @param Authorization $authorithation
     */
    public function __construct(Environment $twig,Session $session, Authorization $authorithation)
    {
        $this->twig = $twig;
        $this->session = $session;
        $this->authorithation = $authorithation;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function execute(ServerRequestInterface $request,ResponseInterface $response): ResponseInterface {
        $body = $this->twig->render('login.twig',[
            'message'=>$this->session->flush('message'),
            'form'=>$this->session->flush('form')
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response): ResponseInterface {
    $params = (array) $request->getParsedBody();
        try{
            $this->authorithation->login($params['email'], $params['password']);
        }catch(AuthorizationException $e){
            $this->session->setData('message',$e->getMessage());
            $this->session->setData('form',$params);
            return $response->withHeader('Location','/login')->withStatus(302);
        }
            return $response->withHeader('Location','/')->withStatus(302);
        }
    }