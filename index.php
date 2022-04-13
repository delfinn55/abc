<?php
ini_set('display_errors', 1);

function report(?string $message = null){
    static $reports = [];

    if ($message === null) {
        echo '<pre>';
        print_r($reports);
        echo '</pre>';
        return $reports;
    } else {
        $reports[] = $message;
    }
}

abstract class Node
{
    abstract protected function sanitize($str) : string;

    abstract protected function getValidateErrors() : array;

    abstract protected function render() : string;

    public function isValid() : bool
    {
        $errors = $this->getValidateErrors();

        if (!empty($errors)) {
            foreach ($errors as $error) {
                report($error);
            }
            return false;
        }

        return true;
    }
}

abstract class Tag extends Node
{
    public string $name;
    protected array $attributes = [];
    protected array $allowed_attributes = [];
    protected array $required_attributes = [];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->allowed_attributes = ['class', 'title'];
    }

    // For tests
    public function setAllowedAttributes(array $list) : object
    {
        $this->allowed_attributes = $list;

        return $this;
    }

    // For tests
    public function setRequiredAttributes(array $list) : object
    {
        $this->required_attributes = $list;

        return $this;
    }

    public function attr($name, $value) : object
    {
        $this->attributes[$name] = $this->sanitize($value);

        return $this;
    }

    protected function sanitize($str) : string
    {
        return htmlspecialchars($str);
    }

    protected function getValidateErrors() : array
    {
        $errors = [];

        // Validate allowed attributes
        foreach (array_keys($this->attributes) as $attr) {
            if (!in_array($attr, $this->allowed_attributes)) {
                $errors[] = 'class[' . self::class . '] -> Attribute "' . $attr . '" is not allowed for "' . $this->name . '"';
            }
        }

        // Validate required attributes
        foreach ($this->required_attributes as $attr) {
            if (!in_array($attr, array_keys($this->attributes))) {
                $errors[] = 'class[' . self::class . '] -> Tag "' . $this->name . '" should contain attribute "' . $attr . '"';
            }
        }

        return $errors;
    }

    protected function renderAttributes($attributes) : string
    {
        $attributes_str = '';
        foreach ($attributes as $name => $value) {
            $attributes_str .= ' ' . $name . '="' . $value . '"';
        }

        return $attributes_str;
    }
}

class SingleTag extends Tag
{
    public function render() : string
    {
        $html = '<' . $this->name;
        $html .= $this->renderAttributes($this->attributes);
        $html .= '>';

        return $html;
    }
}

class PairTag extends Tag
{
    private array $content_nodes;

    public function appendChild(Node $node) : object
    {
        $this->content_nodes[] = $node;

        return $this;
    }

    protected function getValidateErrors() : array
    {
        $errors = parent::getValidateErrors();
        foreach ($this->content_nodes as $node) {
            $errors = array_merge($errors, $node->getValidateErrors());
        }

        return $errors;
    }

    private function renderContent() : string
    {
        $tags_str = '';
        foreach ($this->content_nodes as $node) {
            $tags_str .= $node->render();
        }

        return $tags_str;
    }

    public function render() : string
    {
        $html = '<' . $this->name;
        $html .= $this->renderAttributes($this->attributes);
        $html .= '>';

        $html .= $this->renderContent();

        $html .= '</' . $this->name . '>';

        return $html;
    }
}

class Img extends SingleTag
{
    public function __construct()
    {
        parent::__construct('img');
        $this->allowed_attributes = array_merge($this->allowed_attributes, ['src', 'alt', 'width', 'height']);
        $this->required_attributes = array_merge($this->required_attributes, ['src', 'alt']);
    }
}

class A extends PairTag
{
    public function __construct()
    {
        parent::__construct('a');
        $this->allowed_attributes = array_merge($this->allowed_attributes, ['href']);
        $this->required_attributes = array_merge($this->required_attributes, ['href']);
    }
}

class TextNode extends Node
{
    private string $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    protected function sanitize($str) : string
    {
        return htmlspecialchars($str);
    }

    protected function getValidateErrors() : array
    {
        $errors = [];

        // Validate empty text
        if (empty($this->text)) {
            $errors[] = 'class[' . self::class . '] -> Text is empty';
        }

        return $errors;
    }

    public function render() : string
    {
        return $this->sanitize($this->text);
    }
}

$img = (new Img)
    ->attr('class', 'img')
    ->attr('title', 'image')
    ->attr('src', 'img/1.jpg')
    ->attr('alt', 'Great JPEG')
    ->attr('class', '"><script>alert("Hello");</script><img class="a');

$hr1 = (new SingleTag('hr'))
    ->setAllowedAttributes(['id', 'class4398570384'])
    ->setRequiredAttributes(['style'])
    ->attr('class', 'hr')
    ->attr('id', '34');

$hr2 = (new SingleTag('hr'))
    ->setAllowedAttributes(['id', 'class'])
    ->attr('class', 'hr')
    ->attr('id', '8797');

$t1 = new TextNode('Hello, <script>alert("Hello");</script>, world!');
$t2 = new TextNode('link');
$t3 = new TextNode('');

$h1 = (new PairTag('h2'))->appendChild($img)->appendChild($hr1)->appendChild($t2)->appendChild($t1);
$h2 = new PairTag('h2');

$a = (new A)
    ->attr('class', 'link')
    ->attr('href', 'https://yandex.ru')
    ->appendChild($t2);

$root = (new PairTag('div'))
    ->setAllowedAttributes(['id', 'class'])
    ->setRequiredAttributes(['id'])
    ->attr('class', 'div')
    ->attr('id', '2')
    ->appendChild($img)
    ->appendChild($t1)
    ->appendChild($hr2)
    ->appendChild($a);


if ($root->isValid()) {
    echo $root->render();
} else {
    report();
}
