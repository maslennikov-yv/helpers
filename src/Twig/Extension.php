<?php
namespace Helpers\Twig;


class Extension extends \Twig_Extension
{
    protected $view;

    /**
     * Extension constructor.
     * @param $view
     */
    public function __construct($view)
    {
        $this->view = $view;
    }

    public function getName()
    {
        return 'slim+';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('json_decode', function ($string) {
                return json_decode($string);
            }),
            new \Twig_SimpleFilter('plural', function ($number, $word_1, $word_2, $word_5) {
                //1 предмет, 2 предмета, 5 предметов
                return [$word_1, $word_2, $word_5][$number % 10 == 1 && $number % 100 != 11 ? 0 : ($number % 10 >= 2 && $number % 10 <= 4 && $number % 100 < 10 || $number % 100 >= 20 ? 1 : 2)];
            }),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('table', array($this, 'table'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('paginator', array($this, 'paginator'), array('is_safe' => array('html'))),
        ];
    }

    public function table($data)
    {
        return $this->view->fetch('table/index.phtml', $data);
    }

    public function paginator($data)
    {
        if (empty($data['pagination']) || empty($data['request'])) {
            throw new \Exception('Pagination misconfigured');
        }

        /** @var \Slim\Http\Request $request */
        $request = $data['request'];

        /** @var \Slim\Route $route */
        $route = $request->getAttribute('route');

        $query = $request->getQueryParams();

        return $this->view->fetch('table/paginator.phtml', [
            'pagination' => $data['pagination'],
            'route' => $route->getName(),
            'arguments' => $route->getArguments(),
            'query' => $query ? $query : [],
        ]);
    }
}