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
<!--<!doctype html>-->
<!--<html lang="en">-->
<!--<head>-->
<!--    <meta charset="utf-8">-->
<!--    <meta name="viewport" content="width=device-width, initial-scale=1 shrink-to-fit=no">-->
<!--    <title>Bootstrap demo</title>-->
<!--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">-->
<!--</head>-->
<!--<body>-->
<!--<nav class="navbar navbar-expand-lg bg-body-tertiary">-->
<!--    <div class="container-fluid">-->
<!--        <a class="navbar-brand" href="#">Navbar</a>-->
<!--        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">-->
<!--            <span class="navbar-toggler-icon"></span>-->
<!--        </button>-->
<!--        <div class="collapse navbar-collapse" id="navbarNav">-->
<!--            <ul class="navbar-nav">-->
<!--                <li class="nav-item">-->
<!--                    <a class="nav-link active" aria-current="page" href="#">Home</a>-->
<!--                </li>-->
<!--                <li class="nav-item">-->
<!--                    <a class="nav-link" href="#">Features</a>-->
<!--                </li>-->
<!--                <li class="nav-item">-->
<!--                    <a class="nav-link" href="#">Pricing</a>-->
<!--                </li>-->
<!--                <li class="nav-item">-->
<!--                    <a class="nav-link disabled" aria-disabled="true">Disabled</a>-->
<!--                </li>-->
<!--            </ul>-->
<!--        </div>-->
<!--    </div>-->
<!--</nav>-->
<!--<div class="container">-->
<!--<h1 class="mb-3 mt-3">Hello, world!</h1>-->
<!--<div class="container-fluid row">-->
<!--    <div class="col p-1">-->
<!--        <div class="card" style="width: 18rem;">-->
<!--            <img src="https://dummyimage.com/300x200/4ba34a/004dff" class="card-img-top" alt="...">-->
<!--            <div class="card-body">-->
<!--                <h5 class="card-title">Card title</h5>-->
<!--                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>-->
<!--                <a href="#" class="btn btn-primary">Go somewhere</a>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div class="col p-1">-->
<!--        <div class="card" style="width: 18rem;">-->
<!--            <img src="https://dummyimage.com/300x200/4ba34a/004dff" class="card-img-top" alt="...">-->
<!--            <div class="card-body">-->
<!--                <h5 class="card-title">Card title</h5>-->
<!--                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>-->
<!--                <a href="#" class="btn btn-primary">Go somewhere</a>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div class="col p-1">-->
<!--        <div class="card" style="width: 18rem;">-->
<!--            <img src="https://dummyimage.com/300x200/4ba34a/004dff" class="card-img-top" alt="...">-->
<!--            <div class="card-body">-->
<!--                <h5 class="card-title">Card title</h5>-->
<!--                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>-->
<!--                <a href="#" class="btn btn-primary">Go somewhere</a>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div class="col p-1">-->
<!--        <div class="card" style="width: 18rem;">-->
<!--            <img src="https://dummyimage.com/300x200/4ba34a/004dff" class="card-img-top" alt="...">-->
<!--            <div class="card-body">-->
<!--                <h5 class="card-title">Card title</h5>-->
<!--                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>-->
<!--                <a href="#" class="btn btn-primary">Go somewhere</a>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div class="col p-1">-->
<!--        <div class="card" style="width: 18rem;">-->
<!--            <img src="https://dummyimage.com/300x200/4ba34a/004dff" class="card-img-top" alt="...">-->
<!--            <div class="card-body">-->
<!--                <h5 class="card-title">Card title</h5>-->
<!--                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>-->
<!--                <a href="#" class="btn btn-primary">Go somewhere</a>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div class="col p-1">-->
<!--        <div class="card" style="width: 18rem;">-->
<!--            <img src="https://dummyimage.com/300x200/4ba34a/004dff" class="card-img-top" alt="...">-->
<!--            <div class="card-body">-->
<!--                <h5 class="card-title">Card title</h5>-->
<!--                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>-->
<!--                <a href="#" class="btn btn-primary">Go somewhere</a>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
<!--</div>-->
<!---->
<!--<script src="https://code.jquery.com/ajax/libs/jquery-3.5.1.slim.min.js"></script>-->
<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>-->
<!--</body>-->
<!--</html>-->


<!--/<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.slim.min.map"></script>-->