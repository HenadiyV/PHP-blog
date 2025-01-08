<?php

use App\Authorization;
use App\AuthorizationException;
use App\Session;
use \Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use \Slim\Factory\AppFactory;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use App\Database;

require __DIR__ . '/vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();// аналог $_POST

$session = new Session();
$sessionMiddleware = function (ServerRequestInterface $request, RequestHandlerInterface $handler)use($session) {
    $session->start();
    $response = $handler->handle($request);
    $session->save();
    return $response;
};

$app->add($sessionMiddleware);

$config = include_once 'config/database.php';

$dsn = $config['dsn'];
$username = $config['username'];
$password = $config['password'];

$database = new Database($dsn,$username,$password);

$authorithation = new Authorization($database,$session);

$app->get('/',function(ServerRequestInterface $request,ResponseInterface $response)use($twig,$session){
    $body = $twig->render('index.twig',[
            'user'=>$session->getData('user')
    ]);
    $response->getBody()->write($body);
return $response;
});

$app->get('/login',function(ServerRequestInterface $request,ResponseInterface $response)use($twig,$session){
    $body = $twig->render('login.twig',[
        'message'=>$session->flush('message'),
        'form'=>$session->flush('form')
    ]);
    $response->getBody()->write($body);
return $response;
});
$app->post('/login-post',function(ServerRequestInterface $request,ResponseInterface $response)use($authorithation,$session){
    $params = (array) $request->getParsedBody();
    var_dump($params);
    try{
        $authorithation->login($params['email'], $params['password']);
    }catch(AuthorizationException $e){
        $session->setData('message',$e->getMessage());
        $session->setData('form',$params);
        return $response->withHeader('Location','/login')->withStatus(302);
    }
    return $response->withHeader('Location','/')->withStatus(302);
});

$app->get('/register',function(ServerRequestInterface $request,ResponseInterface $response)use($twig,$session){
    $body = $twig->render('register.twig',[
            'message'=>$session->flush('message'),
            'form'=>$session->flush('form')
    ]);
    $response->getBody()->write($body);
return $response;
});

$app->post('/register-post',function(ServerRequestInterface $request,ResponseInterface $response)use($authorithation,$session){
    $params = (array) $request->getParsedBody();//получаем отправление даные с формы
try{
    $authorithation->register($params);
}catch(AuthorizationException $e){
    $session->setData('message',$e->getMessage());
    $session->setData('form',$params);
    return $response->withHeader('Location','/register')->withStatus(302);
}

    return $response->withHeader('Location','/')->withStatus(302);
});

$app->get('/logout',function(ServerRequestInterface $request,ResponseInterface $response)use($session){
    $session->setData('user',null);
return $response->withHeader('Location','/')->withStatus(302);
});

$app->run();
?>