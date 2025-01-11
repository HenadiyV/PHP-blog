<?php

use App\Middleware\SessionMiddleware;
use App\Route\AboutPage;
use App\Route\BlogPage;
use App\Route\HomePage;
use App\Route\LoginPage;
use App\Route\PostRout;
use App\Route\RegisterPage;
use App\Session;
use App\Slim\TwigMiddleware;
use PhpDevCommunity\DotEnv;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Slim\Factory\AppFactory;
use Twig\Environment;

require __DIR__ . '/vendor/autoload.php';

(new DotEnv(__DIR__ . '/.env'))->load();

$builder = new \DI\ContainerBuilder();
$builder->addDefinitions('config/di.php');

$container= $builder->build();

AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();// аналог $_POST

$session = new Session();

$twig = $container->get(Environment::class);

$app->add(new SessionMiddleware($session));
$app->add(new TwigMiddleware($twig));

$config = include_once 'config/database.php';

$app->get('/', HomePage::class . ':execute');

$app->get('/login', LoginPage::class . ':execute');

$app->post('/login-post', LoginPage::class . ':login');

$app->get('/register', RegisterPage::class . ':execute');

$app->post('/register-post', RegisterPage::class);

$app->get('/logout',function(ServerRequestInterface $request,ResponseInterface $response)use($session){
    $session->setData('user',null);
return $response->withHeader('Location','/')->withStatus(302);
});

$app->get('/about', AboutPage::class);

$app->get('/blog[/{page}]', BlogPage::class);

$app->get('/{url_key}', PostRout::class);

$app->run();
?>