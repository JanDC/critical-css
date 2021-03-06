# Critical CSS

PHP library for generating critical CSS.

![critical css banner](https://critical-css.jandc.io/images/banner.jpg)

## Features

* PHP only, no Node.js required.
* Automatically generated for each page
* Manual control through `{% fold %}{% endfold %}` tags
* Dynamically resolves CSS used on each page

## Site(s) using Critical CSS
* https://www.farmaline.be/apotheek/

## Installation

``composer require jandc/critical-css ``

##### Register the twig extension and create a wrapper instance with the critical CSS processor
```php
 use TwigWrapper\TwigWrapper;
 use CriticalCssProcessor\CriticalCssProcessor;

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
