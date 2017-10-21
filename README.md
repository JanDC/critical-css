# Critical CSS

PHP library for generating critical CSS.

## Features

* Automatically generated for each page
* Manual control through `{% fold %}{% endfold %}` tags
* Dynamically resolves CSS used on each page

## Installation

``composer require jandc/critical-css ``

##### Register the twig extension and create a wrapper instance with the critical CSS processor
```php
 $twigEnvironment->addExtension(new CSSFromHTMLExtractor\Twig\Extension());
 $twigWrapper = new TwigWrapper($twigEnvironment, [new CriticalCssProcessor()]);
 ```
##### Mark the regions of your templates with the provided blocks
```twig
{% fold %}
    <div class="my-class">
    ...
    </div>
{% endfold %}
```

##### Render your pages, using the twigwrapper
```php
 $twigWrapper->render('@templates/my/template.twig', ['foo'=>'bar']);
 ```
 
## Available implementations

* [Silex](https://github.com/JanDC/critical-css-silex)

If you have your own implementation, please send a pull request to add it to this list.
