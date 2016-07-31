<?php
namespace Helpers\Twig;


class PhpRenderer extends \Slim\Views\PhpRenderer
{

    /**
     * @var \Slim\Interfaces\RouterInterface
     */
    private $router;

    /**
     * PhpRenderer constructor.
     *
     * @param \Slim\Interfaces\RouterInterface $router
     * @param array $attributes
     */
    public function __construct(\Slim\Interfaces\RouterInterface $router, $attributes = [])
    {
        $this->router = $router;
        $templatePath = realpath(__DIR__ . '/../../views');
        parent::__construct($templatePath, $attributes);
    }


    /**
     * Escape a string for the HTML Body context where there are very few characters
     * of special meaning. Internally this will use htmlspecialchars().
     *
     * @param string $string
     * @return string
     */
    public function escapeHtml($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, $this->encoding);
    }

    /**
     * Render attributes
     *
     * @param mixed $attr
     * @param string $value
     * @return string
     */
    public function attributes($attr, $value = null)
    {
        if (is_array($attr)) {
            $attributes = [];
            foreach ($attr as $k => $v) {
                $attributes[] = $this->attributes($k, is_array($v) ? implode(' ', $v) : $v);
            }
            return count($attributes) ? ' ' . implode(' ', $attributes) : '';
        } else if ($attr) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }
            return sprintf('%s="%s"', $this->escapeHtml($attr), $this->escapeHtml($value));
        }
        return '';
    }

    /**
     * Render form element
     *
     * @param $el
     * @return string
     */
    public function formField($el)
    {
        $field = '';

        switch ($el['type']) {
            case 'text':
            case 'hidden':
            case 'password':
            case 'radio':
            case 'checkbox':
            case 'file':
            case 'submit':
                $field = sprintf("<input%s>\n", $this->attributes($el));
                break;
            case 'select':
                $value_options = isset($el['value_options']) && is_array($el['value_options']) ? $el['value_options'] : [];
                $value = isset($el['value']) ? $el['value'] : null;
                unset($el['value_options']);
                unset($el['value']);
                foreach ($value_options as $option_value => $option) {
                    $attr = [
                        'value' => $option_value,
                    ];
                    if ($value !== null && $value == $option_value) {
                        $attr['selected'] = 'selected';
                    }
                    $field .= sprintf("    <option%s>%s</option>\n", $this->attributes($attr), $this->escapeHtml($option));
                }
                $field = sprintf("<select%s>\n%s</select>\n", $this->attributes($el), $field);
                break;
            case 'textarea':
                $value = isset($el['value']) ? $el['value'] : '';
                unset($el['value']);
                $field = sprintf("<textarea%s>%s</textarea>\n", $this->attributes($el), $this->escapeHtml($value));
                break;
            default:

        }
        return $field;
    }

    /**
     * A Yahoo! Search-like scrolling style.  The cursor will advance to
     * the middle of the range, then remain there until the user reaches
     * the end of the page set, at which point it will continue on to
     * the end of the range and the last page in the set.
     *
     * @param $pagination
     * @param null $pageRange
     * @return array
     */
    public function getPages($pagination, $pageRange = null)
    {
        if ($pageRange === null) {
            $pageRange = 10;
        }

        $pageNumber = $pagination['current_page'];
        $pageCount = $pagination['last_page'];

        if ($pageRange > $pageCount) {
            $pageRange = $pageCount;
        }

        $delta = ceil($pageRange / 2);

        if ($pageNumber - $delta > $pageCount - $pageRange) {
            $lowerBound = $pageCount - $pageRange + 1;
            $upperBound = $pageCount;
        } else {
            if ($pageNumber - $delta < 0) {
                $delta = $pageNumber;
            }

            $offset = $pageNumber - $delta;
            $lowerBound = $offset + 1;
            $upperBound = $offset + $pageRange;
        }

        $lowerBound = $this->normalizePageNumber($lowerBound, $pageCount);
        $upperBound = $this->normalizePageNumber($upperBound, $pageCount);

        $pages = [];

        for ($pageNumber = $lowerBound; $pageNumber <= $upperBound; $pageNumber++) {
            $pages[$pageNumber] = $pageNumber;
        }

        return $pages;
    }

    /**
     * Brings the page number in range of the paginator.
     *
     * @param $pageNumber
     * @param $pageCount
     * @return int
     */
    public function normalizePageNumber($pageNumber, $pageCount)
    {
        $pageNumber = (int)$pageNumber;

        if ($pageNumber < 1) {
            $pageNumber = 1;
        }

        if ($pageCount > 0 && $pageNumber > $pageCount) {
            $pageNumber = $pageCount;
        }

        return $pageNumber;
    }

    /**
     * Assemble url
     *
     * @param $name
     * @param array $data
     * @param array $queryParams
     * @return string
     */
    public function pathFor($name, $data = [], $queryParams = [])
    {
        return $this->router->pathFor($name, $data, $queryParams);
    }

}