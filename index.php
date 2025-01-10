<?php

use App\Authorization;
use App\AuthorizationException;
use App\LatestPost;
use App\PostMapper;
use App\Session;
//use App\Twig\AssetExtension;
use App\Slim\TwigMiddleware;
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
//$twig->addExtension(new AssetExtension());
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
$app->add(new TwigMiddleware($twig));

$config = include_once 'config/database.php';

$dsn = $config['dsn'];
$username = $config['username'];
$password = $config['password'];

$database = new Database($dsn,$username,$password);

$authorithation = new Authorization($database,$session);

//$postMapper = new PostMapper($database);

//$app->get('/',function(ServerRequestInterface $request,ResponseInterface $response, $args)use($twig,$session,$postMapper){
//    $posts = $postMapper->getList('ASC');
//    $body = $twig->render('index.twig',[
//            'user'=>$session->getData('user'),
//        'posts'=>$posts
//    ]);
//    $response->getBody()->write($body);
//return $response;
//});

$app->get('/',function(ServerRequestInterface $request,ResponseInterface $response)use($twig,$session,$database){
    $latestPosts = new LatestPost($database);
    $body = $twig->render('index.twig',[
        'user'=>$session->getData('user'),
        'posts'=>$latestPosts->get(4)
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

$app->get('/about',function(ServerRequestInterface $request,ResponseInterface $response)use($twig,$session){

    $body = $twig->render('about.twig',[
        'user'=>$session->getData('user'),
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/blog[/{page}]',function(ServerRequestInterface $request,ResponseInterface $response,$args)use($twig,$session,$database){
    $postMapper = new PostMapper($database);
    $page = isset($args['page']) ? (int) $args['page']:1;
    $limit = 2;
    try{
        $posts = $postMapper->getList($page,$limit,'DESC');
        $totalCount = $postMapper->getTotalCount();
        $body = $twig->render('blog.twig',[
            'user'=>$session->getData('user'),
            'posts'=>$posts,
            'pagination' => [
                'current' => $page,
                'paging' => ceil($totalCount / $limit)
            ]
        ]);
        $response->getBody()->write($body);
        return $response;
    }catch(Exception $e){
        //throw new Exception('Page not found.');
        return $response->withHeader('Location','/blog')->withStatus(302);
    }

});


$app->get('/{url_key}',function(ServerRequestInterface $request,ResponseInterface $response, $args)use($twig,$session,$database){
    $postMapper = new PostMapper($database);
    $post = $postMapper->getByUrlKey((string) $args['url_key']);

    if(empty($post)){
        $body = $twig->render('not-found.twig');
    }else{
    $body = $twig->render('post.twig',[
        'url_key'=>$args['url_key'],
        'user'=>$session->getData('user'),
        'post'=>$post
    ]);
    }
    $response->getBody()->write($body);
    return $response;

});

$app->run();
?>