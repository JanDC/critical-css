<?php

namespace CriticalCssProcessor;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use DOMDocument;
use DOMElement;
use Masterminds\HTML5;
use Twig_Environment;
use Twig_Error_Runtime;
use CSSFromHTMLExtractor\Twig\Extension as ExtractorExtension;
use TwigWrapper\PostProcessorInterface;

class CriticalCssProcessor implements PostProcessorInterface
{

    /** @var Cache */
    private $fileCache;

    public function __construct(Cache $fileCache = null)
    {
        $this->fileCache = $fileCache;
        if (is_null($fileCache)) {
            $this->fileCache = new ArrayCache();
        }

    }

    /**
     * @param string $rawHtml
     *
     * @param string $name Template name
     * @param array $context The context used to render the template
     * @param Twig_Environment|null $environment The twig environment used, useful for accessing
     *
     * @return string processedHtml
     */
    public function process($rawHtml, $name = '', $context = [], $environment = null)
    {

        try {
            $html5 = new HTML5();
            $document = $html5->loadHTML($rawHtml);

            /** @var ExtractorExtension $extractorExtension */
            $extractorExtension = $environment->getExtension(ExtractorExtension::class);
            foreach ($document->getElementsByTagName('link') as $linkTag) {
                /** @var DOMElement $linkTag */
                if ($linkTag->getAttribute('rel') == 'stylesheet') {
                    $tokenisedStylesheet = explode('?', $linkTag->getAttribute('href'));
                    $stylesheet = reset($tokenisedStylesheet);

                    if ($content = $this->fileCache->fetch($stylesheet)) {
                        $extractorExtension->addBaseRules($content);
                        continue;
                    }


                    if (($content = @file_get_contents($stylesheet)) !== false) {
                        $this->fileCache->save($stylesheet, $content);
                        $extractorExtension->addBaseRules($content);
                        continue;
                    }
                    if (($content = @file_get_contents($_SERVER['DOCUMENT_ROOT'] . $stylesheet)) !== false) {
                        $this->fileCache->save($stylesheet, $content);
                        $extractorExtension->addBaseRules($content);
                        continue;
                    }
                }
            }

        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return $rawHtml;
        }

        try {
            $criticalCss = $extractorExtension->buildCriticalCssFromSnippets();
            if (strlen($criticalCss) == 0) {
                return $rawHtml;
            }
        } catch (Twig_Error_Runtime $tew) {
            error_log($tew->getMessage());
            return $rawHtml;
        }

        try {
            $headStyle = new DOMElement('style', $criticalCss);
            $document->getElementsByTagName('head')->item(0)->appendChild($headStyle);

            return $html5->saveHTML($document);
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return $rawHtml;
        }
    }
}