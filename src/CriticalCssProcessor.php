<?php

namespace CriticalCssProcessor;

use DOMDocument;
use DOMElement;
use Twig_Environment;
use Twig_Error_Runtime;
use CSSFromHTMLExtractor\Twig\Extension as ExtractorExtension;
use TwigWrapper\PostProcessorInterface;

class CriticalCssProcessor implements PostProcessorInterface
{

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
            $document = new DOMDocument();
            $internalErrors = libxml_use_internal_errors(true);
            $document->loadHTML(mb_convert_encoding($rawHtml, 'HTML-ENTITIES', 'UTF-8'));
            libxml_use_internal_errors($internalErrors);
            $document->formatOutput = true;
            /** @var ExtractorExtension $extractorExtension */
            $extractorExtension = $environment->getExtension(ExtractorExtension::class);
            foreach ($document->getElementsByTagName('link') as $linkTag) {
                /** @var DOMElement $linkTag */
                if ($linkTag->getAttribute('rel') == 'stylesheet') {
                    $stylesheet = $linkTag->getAttribute('href');

                    $stylesheet = reset(explode('?', $stylesheet));

                    if (($content = @file_get_contents($stylesheet)) !== false) {
                        $extractorExtension->addBaseRules($content);
                        continue;
                    }
                    if (($content = @file_get_contents($_SERVER['DOCUMENT_ROOT'] . $stylesheet)) !== false) {
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
            return $document->saveHTML();
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return $rawHtml;
        }
    }
}