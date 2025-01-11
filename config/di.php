<?php

use App\Authorization;
use App\Database;
use App\Session;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use function DI\autowire;
use function DI\get;

return [
    FilesystemLoader::class => autowire()
        ->constructorParameter('paths','templates'),

    Environment::class => autowire()
        ->constructorParameter('loader', get(FilesystemLoader::class)),

    Database::class => autowire()
        ->constructorParameter('connection',get(PDO::class)),

    PDO::class => autowire()
        ->constructorParameter('dsn',getenv('DATABASE_DSN'))
        ->constructorParameter('username',getenv('DATABASE_USERNAME'))
        ->constructorParameter('password',getenv('DATABASE_PASSWORD'))
        ->constructorParameter('options',[]),

    Session::class=>autowire()->constructor(),

    Authorization::class => autowire()
        ->constructorParameter('database', get(Database::class))
        ->constructorParameter('session', get(Session::class))
];