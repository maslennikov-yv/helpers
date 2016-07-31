# Twig extension with various helpers

(only Table ready so far)

##Use with Slim3:

```php
$container = $app->getContainer();

// Twig

$container['view'] = function ($c) {
    $view = new Slim\Views\Twig($template_path, []);
    
    // Add helpers
    $view->addExtension(new Helpers\Twig\Extension(new Helpers\Twig\PhpRenderer($c->get('router'))));

    return $view;
};
```

###Action:

```php
public function __invoke(Request $request, Response $response, $args)
{
    $this->logger->info("Home page action dispatched");

    $page = (int)$request->getParam('page', 1);

    $this->view->render($response, 'home.twig', [
        'table' => [
            'columns' => [
                [
                    'header' => [
                        'html' => 'Id',
                    ],
                    'name' => 'id',
                ],
                [
                    'header' => [
                        'html' => 'Name',
                    ],
                    'callback' => function ($view, $row) {
                        return $view->escapeHtml($row['name']);
                    },
                ],
            ],
            'pagination' => [
                'total' => 2,
                'per_page' => 1,
                'current_page' => $page,
                'last_page' => 2,
                'next_page' => $page == 1 ? 2 : null,
                'prev_page' => $page == 2 ? 1 : null,
                'from' => $page,
                'to' => $page,
            ],
            'request' => $request,
            'rows' => [
                [
                    'id' => 1,
                    'name' => 'Amelia',
                ],
                [
                    'id' => 2,
                    'name' => 'Antonio',
                ],
            ],
        ],
    ]);
    return $response;
}
```

###home.twig:

```twig
{{ table(table) }}
{{ paginator(table) }}
```
