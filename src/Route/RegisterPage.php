<?php

namespace App\Route;

use App\Authorization;
use App\AuthorizationException;
use App\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class RegisterPage
{
    /**
     * @var Environment
     */
    private Environment $twig;
    /**
     * @var Session
     */
    private Session $session;

    private Authorization $authorithation;


    public function __construct(Environment $twig,Session $session,Authorization $authorithation)
    {
        $this->twig = $twig;
        $this->session = $session;
        $this->authorithation = $authorithation;
    }

    public function execute(ServerRequestInterface $request,ResponseInterface $response): ResponseInterface {
    $body = $this->twig->render('register.twig',[
            'message'=>$this->session->flush('message'),
            'form'=>$this->session->flush('form')
    ]);
    $response->getBody()->write($body);
    return $response;
    }

    public function __invoke(ServerRequestInterface $request,ResponseInterface $response): ResponseInterface {
        $params = (array) $request->getParsedBody();//получаем отправление даные с формы
        try{
            $this->authorithation->register($params);
        }catch(AuthorizationException $e){
            $this->session->setData('message',$e->getMessage());
            $this->session->setData('form',$params);
            return $response->withHeader('Location','/register')->withStatus(302);
        }

        return $response->withHeader('Location','/')->withStatus(302);
    }
}