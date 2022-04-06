<?php
ini_set('display_errors', 1);

class Tag
{
    protected string $name;
    protected array $attributes;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->attributes = [];
    }

    public function attr($name, $value) : object
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    protected function implodeAttributes($attributes) : string
    {
        $attributes_str = '';
        foreach ($attributes as $name => $value) {
            $attributes_str .= ' ' . $name . '="' . $value . '"';
        }

        return $attributes_str;
    }

    /**
     * Without this func I had error:
     * "Potentially polymorphic call. Tag does not have members in its hierarchy"
     */
    protected function render() : string
    {
        return '';
    }
}

class SingleTag extends Tag
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function render() : string
    {
        $html = '<' . $this->name;
        $html .= $this->implodeAttributes($this->attributes);
        $html .= '>';

        return $html;
    }
}

class PairTag extends Tag
{
    private string $content;
    private array $content_tags;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->content = '';
    }

    public function appendChild(Tag $tag) : object
    {
        $this->content_tags[] = $tag;

        return $this;
    }

    private function parseContent($tags) : string
    {
        $tags_str = '';
        foreach ($this->content_tags as $tag) {
            $tags_str .= $tag->render();
        }

        return $tags_str;
    }

    public function render() : string
    {
        $html = '<' . $this->name;
        $html .= $this->implodeAttributes($this->attributes);
        $html .= '>';

        $html .= $this->parseContent($this->content_tags);

        $html .= '</' . $this->name . '>';

        return $html;
    }
}

//$img = new SingleTag('img');
//$img->attr('src', 'img/banner.jpg')
//    ->attr('style', 'width: 100px;')
//    ->attr('alt', 'nz');
//
//$hr = new SingleTag('hr');
//
//$a = new PairTag('a');
//echo $a->attr('href', './nz')
//    ->appendChild($hr)
//    ->appendChild($img)
//    ->appendChild($hr)
//    ->render();


// Test
function forTest() : PairTag
{
    // Label #1 block
    $label1 = new PairTag('label');

    $img1 = new SingleTag('img');
    $img1->attr('src', 'f1.jpg')
        ->attr('alt', 'f1 not found');

    $input1 = new SingleTag('input');
    $input1->attr('type', 'text')
        ->attr('name', 'f1');

    $label1->appendChild($img1)->appendChild($input1);

    // Label #2
    $label2 = new PairTag('label');

    $img2 = new SingleTag('img');
    $img2->attr('src', 'f2.jpg')
        ->attr('alt', 'f2 not found');

    $input2 = new SingleTag('input');
    $input2->attr('type', 'password')
        ->attr('name', 'f2');

    $label2->appendChild($img2)->appendChild($input2);

    // Submit
    $submit = new SingleTag('input');
    $submit->attr('type', 'submit')->attr('value', 'Send');

    // Form
    $form = new PairTag('form');
    return $form->appendChild($label1)->appendChild($label2)->appendChild($submit);
}

echo forTest()->render();





