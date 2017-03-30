<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();


$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'host'     => 'localhost:3360',
        'dbname'   => 'ppn_blog',
        'user'     => 'root',
        'password' => 'root',
        'charset'  => 'utf8mb4',
    ),
));

$app['blogposts'] = $app['db']->fetchAll('SELECT * FROM blog_posts BP INNER JOIN blog_authors BA ON BP.authorID = BA.authorID ORDER BY BP.postDate DESC');

$app->get('/', function (Silex\Application $app)  {
    return $app['twig']->render(
        'index.html.twig',
        array(
            'blogposts' => $app['blogposts'],
        )
    );
})->bind('index');

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../templates',
));

$app->get('/post/{id}', function (Silex\Application $app, $id)  {
    if (!array_key_exists($id, $app['blogposts'])) {
        $app->abort(404, 'The article could not be found');
    }
    $article = $app['blogposts'][$id];
    return $app['twig']->render(
        'blogpost.html.twig',
        array(
            'article' => $article,
        )
    );
})
    ->assert('id', '\d+')
    ->bind('blogpost');

$app->get('/archive/{year}/{month}', function (Silex\Application $app, $year, $month) {
    $arr = array();

    foreach ($app['blogposts'] as $article) {
        $postMonth = $article.postDate->format('m');
        $postYear = $article.postDate->format('Y');
        if ($postYear == $year && $postMonth == $month) {
           array_push($arr, $article);
        }
    }
    return $app['twig']->render(
        'archive.html.twig',
        array(
            'blogposts' => $arr,
        )
    );
})->assert('year', '\d{4}')
  ->assert('month', '\d{2}')
  ->bind('archive');



$app->register(new Silex\Provider\UrlGeneratorServiceProvider());




// This should be the last line
$app->run(); // Start the application, i.e. handle the request
?>