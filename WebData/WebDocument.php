<?php

namespace App\AnuVi\WebData;

use \Symfony\Component\DomCrawler\Crawler;

# http://metaoptimize.com/blog/2010/08/18/kea-keyphrase-extraction-as-an-xml-rpc-service/

// see: https://www.readability.com/developers/guidelines

/**
 * Class WebDocument
 * @package packages\AnuVi\WebData
 */
class WebDocument
{
    // extract meta data from source code

    // # class WebDocument extends Document
    // # class HtmlSourceCode extends SourceCode
    // ✔ getMetaDescription()
    // ✔ getSourceCodeFromUrl()
    // ✔ getLinks()
    // get Paragraphs()
    // get Images()
    // ✔ getHeaders()
    // getKeywords()
    // ✔ getContent()
    // getSentences()
    // getSemanticData()


    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;

    /**
     * @var array;
     */
    protected $junkNodes = [
        "style", "form", "iframe", "script", "button", "input", "textarea",
        "noscript", "select", "option", "object", "applet", "basefont",
        "bgsound", "blink", "canvas", "command", "menu", "nav", "datalist",
        "embed", "frame", "frameset", "keygen", "label", "marquee", "script"
        // "link"?
    ];


    protected $headerSelectors = [
        "h1", "h2", "h3", "h4", "h5"
    ];


    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->crawler = new Crawler();
    }


    public function setHtmlContent($htmlContent)
    {
        // $htmlContent = self::cleanDocument($htmlContent);

        // reset crawler data
        $this->crawler->clear();
        $this->crawler->addHtmlContent($htmlContent);
    }


    public function getHtmlContent()
    {
        $htmlContent = $this->crawler->html();

        $htmlContent = $this->tidyHtml($htmlContent);

        return $htmlContent;
    }


    private function tidyHtml($htmlContent, $encoding = "utf8")
    {
        if (function_exists('tidy_repair_string'))
        {
            $options = array
            (
                'anchor-as-name' => false,
                'break-before-br' => true,
                'char-encoding' => $encoding,
                'decorate-inferred-ul' => false,
                'doctype' => 'omit',
                'drop-font-tags' => true,
                'drop-proprietary-attributes' => false,
                'force-output' => false,
                'indent' => true,
                'indent-attributes' => false,
                'indent-spaces' => 2,
                'input-encoding' => $encoding,
                'join-styles' => false,
                'merge-divs' => false,
                'merge-spans' => false,
                'new-blocklevel-tags' => 'article aside audio bdi canvas details dialog figcaption figure footer header hgroup main menu menuitem nav section source summary template track video',
                'new-empty-tags' => 'command embed keygen source track wbr',
                'new-inline-tags' => 'audio command datalist embed keygen mark menuitem meter output progress source time video wbr',
                'newline' => 0,
                'numeric-entities' => false,
                'output-bom' => false,
                'output-encoding' => $encoding,
                'output-html' => true,
                'quiet' => true,
                'quote-ampersand' => true,
                'quote-marks' => false,
                'repeated-attributes' => 1,
                'show-warnings' => false,
                'sort-attributes' => 1,
                'tab-size' => 4,
                'tidy-mark' => false,
                'vertical-space' => true,
                'wrap' => 0,

                'enclose-block-text' => true,
                'drop-empty-paras' => true,
                'hide-comments' => true,
                'enclose-text' => true,
                'logical-emphasis' => true,
                'preserve-entities' => true,
            );

            // $htmlContent = tidy_repair_string($htmlContent, $options, $encoding);
            $tidy = tidy_parse_string($htmlContent, $options, $encoding);
            $tidy->cleanRepair();
            $htmlContent = $tidy->value;

            if (!empty($htmlContent))
            {
                return $htmlContent;
            }
        }

        return false;
    }


    public function getDocumentData()
    {
        $documentData = [
            'Title' => $this->getTitle(),
            'MetaDescription' => $this->getMetaDescription(),
            'Headers' => $this->getHeaders(),
            'Links' => $this->getLinks(),
            'Article' => $this->getArticle()
            // Breadcrumbs
        ];

        return $documentData;
    }


    public function getTitle()
    {
        try
        {
            $title = $this->crawler->filter('title')->text();
        }
        catch(\Exception $e)
        {
            // ignore exception, set empty title
            $title = "";
        }

        $title = trim($title);

        return $title;
    }


    public function getMetaDescription()
    {
        $metaDescription = $this->crawler->filterXpath('//meta[@name="description"]')->attr('content');

        $metaDescription = trim($metaDescription);

        return $metaDescription;
    }


    public function getHeaders()
    {
        $headers = [];

        foreach($this->headerSelectors as $headerSelector)
        {
            $headers[$headerSelector] = $this->crawler->filter($headerSelector)->extract(['_text']);

            $headers[$headerSelector] = array_map(function ($header) {
                $header = preg_replace("/\r|\n/", " ", $header);

                return $header;
            }, $headers[$headerSelector]);
        }

        return $headers;
    }


    public function getLinks()
    {
        $links = $this->crawler->filter("a")->each(function (Crawler $nodeCrawler)
        {
            $linkData = [
                'LinkUrl' => trim(preg_replace("/\n|\r/", " ", $nodeCrawler->attr("href"))),
                'LinkText' => trim(preg_replace("/\n|\r/", " ", $nodeCrawler->text())),
                'LinkTitle' => trim(preg_replace("/\n|\r/", " ", $nodeCrawler->attr("title")))
            ];

            if(empty($linkData['LinkText']))
            {
                // the link has no text, check if an image is nested

                // TODO: improve algorithm
                // get alt attribute of image tag within the current anker
                $imageHtml = $nodeCrawler->html();

                $hasImageAltMatches = preg_match("/alt=\"(?'ImageAlt'.*)\"/iU", $imageHtml, $imageAltMatches);

                $imageAltText = "";
                if($hasImageAltMatches)
                {
                    $imageAltText = $imageAltMatches['ImageAlt'];
                }

                $linkData['LinkText'] = trim(preg_replace("/\n|\r/", " ", $imageAltText, true));
            }

            return $linkData;
        });

        return $links;
    }


    /**
     * Returns the main text article of the current HTML page.
     *
     * @return string
     */
    public function getArticle()
    {
        // remove wikipedia junk nodes
        $this->removeJunkNodes([
            '.menu', 'nav', '#toc', '#Weblinks', '#mw-navigation', '#footer', '.mw-editsection',
            '#catlinks', '#Vorlage_Gesprochene_Version', '.printfooter', '#breadcrumb', 'header',
            '.comments', 'div[role=complementary]', '.sidebar', 'footer', '.footer-bottom',
            '#comments', 'aside', '.sidebar', '.post-navigation', '.share-post', '#related_posts',
            '.updated', '.author', '.vcard', '#crumbs'
        ]);

        // get raw text of nodes
        $article = $this->getText();

        // remove tabs in article
        $article = trim(str_replace(["\t"], "", $article));

        // remove empty lines in article
        $article = implode("\n", array_filter(explode("\n", $article)));

        return $article;
    }

    /*
     * see: https://github.com/jiminoc/goose/wiki
     * We use a scoring system based on clustering of English stop words and other factors that you can
     * find in the code. We also do descending scoring so as the nodes move down the lower their scores become.
     * The goal is to find the strongest grouping of text nodes inside a parent container and assume that's your
     * group of content as long as it's high enough up on the page.
     */

    public static function cleanDocument($htmlContent)
    {
        $htmlContent = trim($htmlContent);

        // If we've got Tidy, let's clean up input.
        // This step is highly recommended - PHP's default HTML parser
        // often doesn't do a great job and results in strange output.
        if (function_exists('tidy_parse_string'))
        {
            $tidy = tidy_parse_string($htmlContent, array('indent'=>true), 'UTF8');
            $tidy->cleanRepair();
            $htmlContent = $tidy->value;
        }

        // replace all doubled-up <br> tags with <p> tags
        $htmlContent = preg_replace("/<br\\/?>[ \r\n\\s]*<br\\/?>/i", "</p><p>", $htmlContent);

        /*
        cleanup steps:
        prepDocument
        Remove all stylesheets
        Turn all double br's into p's
        grabArticle
        grabArticle nodePrepping
        grabArticle calculate scores
        getInnerText
        grabArticle find top candidate
        getLinkDensity
        grabArticle look through its siblings
        prepArticle
        cleanConditionally
        prepArticle Remove extra paragraphs
        prepArticle innerHTML replacement

        https://github.com/jiminoc/goose

        We calculate what’s interesting on the web in real-time. As new content is published across the web
        Gravity crawls and semantically analyzes each web page and tracks various performance metrics.

        Gravity uses a weighted edge ontology that has nodes based on DBpedia.
        We map all topics and interests focused on relevancy and accuracy.
        */

        return trim($htmlContent);
    }


    public function getText()
    {
        $text = $this->crawler->text();

        return $text;
    }


    public function removeJunkNodes($additionalJunkNodes = [])
    {
        $allJunkNodes = array_merge($this->junkNodes, $additionalJunkNodes);

        $junkNodes = join(",", $allJunkNodes);

        $this->crawler->filter($junkNodes)->each(function (Crawler $nodeCrawler)
        {
            foreach ($nodeCrawler as $node)
            {
                $node->parentNode->removeChild($node);
            }
        });
    }


    public function getNodeValues($nodeSearchQuery)
    {
        $nodeValues = $this->crawler->filter($nodeSearchQuery)->each(function (Crawler $nodeCrawler)
        {
            return $nodeCrawler->text();
        });

        return $nodeValues;
    }


    public function getNodesHtml($nodeSearchQuery)
    {
        $nodeHtml = $this->crawler->filter($nodeSearchQuery)->each(function (Crawler $nodeCrawler)
        {
            return $nodeCrawler->parents()->first()->html();
        });

        return $nodeHtml;
    }


    public function getNodeAttributeValues( $nodeSearchQuery, $attributeName )
    {
    	$nodeAttributeValues = $this->crawler->filter( $nodeSearchQuery )->each( function ( Crawler $nodeCrawler ) use ( $attributeName )
        {
            return $nodeCrawler->attr( $attributeName );
        });

        return $nodeAttributeValues;
    }
}
