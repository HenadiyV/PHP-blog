<?php

use App\Middleware\SessionMiddleware;
use App\Route\AboutPage;
use App\Route\BlogPage;
use App\Route\HomePage;
use App\Route\LoginPage;
use App\Route\LogoutPage;
use App\Route\PostRout;
use App\Route\RegisterPage;
use PhpDevCommunity\DotEnv;
use \Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

(new DotEnv(__DIR__ . '/.env'))->load();

$builder = new \DI\ContainerBuilder();
$builder->addDefinitions('config/di.php');

$container= $builder->build();

AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();// аналог $_POST

$app->add($container->get(SessionMiddleware::class));

$app->get('/', HomePage::class . ':execute');

$app->get('/login', LoginPage::class . ':execute');

$app->post('/login-post', LoginPage::class);

$app->get('/register', RegisterPage::class . ':execute');

$app->post('/register-post', RegisterPage::class);

$app->get('/logout', LogoutPage::class);

$app->get('/about', AboutPage::class);

$app->get('/blog[/{page}]', BlogPage::class);

$app->get('/{url_key}', PostRout::class);

$app->run();
?>