<?php

namespace App\AnuVi\TextAnalysis;


class TextAnalyzer
{
    /**
     * @var string The text which should be analyzed.
     */
    protected $text;


    protected $wordCharList = 'äüöÄÜÖß-';


    protected $ignoreCharList = [
        "\t", "\n", "\r", "\0", "\x0B", "\xc2", "\xa0"
    ];


    protected $specialCharList = [
        'Original' => ["ä", "ü", "ö", "Ä", "Ü", "Ö"],
        'Replace' => [ "ae", "ue", "oe", "Ae", "Ue", "Oe" ]
    ];


    protected $signs = [
        ',', '.', '!', '"', '?', '[', ']', '\n', '\r', '=', '»', '%', '*', '&', ' - ', '·', '>', '•'
    ];


    protected $stopWords = [
        "ab",  "bei",  "da",  "deshalb",  "ein",  "für",  "finde",  "haben",  "hier",  "ich",  "ja",
        "kann",  "machen",  "muesste",  "nach",  "oder",  "seid",  "sonst",  "und",  "vom",  "wann",  "wenn",
        "wie",  "zu",  "bin",  "eines",  "hat",  "manche",  "solches",  "an",  "anderm",  "bis",  "das",  "deinem",
        "demselben",  "dir",  "doch",  "einig",  "er",  "eurer",  "hatte",  "ihnen",  "ihre",  "ins",  "jenen",
        "keinen",  "manchem",  "meinen",  "nichts",  "seine",  "soll",  "unserm",  "welche",  "werden",  "wollte",
        "während",  "alle",  "allem",  "allen",  "aller",  "alles",  "als",  "also",  "am",  "ander",  "andere",
        "anderem",  "anderen",  "anderer",  "anderes",  "andern",  "anders",  "auch",  "auf",  "aus",  "bist",
        "bsp.",  "daher",  "damit",  "dann",  "dasselbe",  "dazu",  "daß",  "dein",  "deine",  "deinen",
        "deiner",  "deines",  "dem",  "den",  "denn",  "denselben",  "der",  "derer",  "derselbe",
        "derselben",  "des",  "desselben",  "dessen",  "dich",  "die",  "dies",  "diese",  "dieselbe",
        "dieselben",  "diesem",  "diesen",  "dieser",  "dieses",  "dort",  "du",  "durch",  "eine",  "einem",
        "einen",  "einer",  "einige",  "einigem",  "einigen",  "einiger",  "einiges",  "einmal",  "es",  "etwas",
        "euch",  "euer",  "eure",  "eurem",  "euren",  "eures",  "ganz",  "ganze",  "ganzen",  "ganzer",
        "ganzes",  "gegen",  "gemacht",  "gesagt",  "gesehen",  "gewesen",  "gewollt",  "hab",  "habe",
        "hatten",  "hin",  "hinter",  "ihm",  "ihn",  "ihr",  "ihrem",  "ihren",  "ihrer",  "ihres",
        "im",  "in",  "indem",  "ist",  "jede",  "jedem",  "jeden",  "jeder",  "jedes",  "jene",  "jenem",
        "jener",  "jenes",  "jetzt",  "kein",  "keine",  "keinem",  "keiner",  "keines",  "konnte",  "könnten",
        "können",  "könnte",  "mache",  "machst",  "macht",  "machte",  "machten",  "man",  "manchen",  "mancher",
        "manches",  "mein",  "meine",  "meinem",  "meiner",  "meines",  "mich",  "mir",  "mit",  "muss",
        "musste",  "müßt",  "nicht",  "noch",  "nun",  "nur",  "ob",  "ohne",  "sage",  "sagen",  "sagt",
        "sagte",  "sagten",  "sagtest",  "sehe",  "sehen",  "sehr",  "seht",  "sein",  "seinem",  "seinen",
        "seiner",  "seines",  "selbst",  "sich",  "sicher",  "sie",  "sind",  "so",  "solche",  "solchem",
        "solchen",  "solcher",  "sollte",  "sondern",  "um",  "uns",  "unse",  "unsen",  "unser",  "unses",
        "unter",  "viel",  "von",  "vor",  "war",  "waren",  "warst",  "was",  "weg",  "weil",  "weiter",
        "welchem",  "welchen",  "welcher",  "welches",  "welche",  "werde",  "wieder",  "will",  "wir",  "wird",
        "wirst",  "wo",  "wolle",  "wollen",  "wollt",  "wollten",  "wolltest",  "wolltet",  "würde",  "würden",
        "z.B.",  "zum",  "zur",  "zwar",  "zwischen",  "über",  "aber",  "abgerufen",  "abgerufene",
        "abgerufener",  "abgerufenes",  "acht",  "allein",  "allerdings",  "allerlei",  "allgemein",
        "allmählich",  "allzu",  "alsbald",  "andererseits",  "andernfalls",  "anerkannt",  "anerkannte",
        "anerkannter",  "anerkanntes",  "anfangen",  "anfing",  "angefangen",  "angesetze",  "angesetzt",
        "angesetzten",  "angesetzter",  "ansetzen",  "anstatt",  "arbeiten",  "aufgehört",  "aufgrund",
        "aufhören",  "aufhörte",  "aufzusuchen",  "ausdrücken",  "ausdrückt",  "ausdrückte",  "ausgenommen",
        "ausser",  "ausserdem",  "author",  "autor",  "außen",  "außer",  "außerdem",  "außerhalb",  "bald",
        "bearbeite",  "bearbeiten",  "bearbeitete",  "bearbeiteten",  "bedarf",  "bedurfte",  "bedürfen",
        "befragen",  "befragte",  "befragten",  "befragter",  "begann",  "beginnen",  "begonnen",  "behalten",
        "behielt",  "beide",  "beiden",  "beiderlei",  "beides",  "beim",  "bei",  "beinahe",  "beitragen",
        "beitrugen",  "bekannt",  "bekannte",  "bekannter",  "bekennen",  "benutzt",  "bereits",  "berichten",
        "berichtet",  "berichtete",  "berichteten",  "besonders",  "besser",  "bestehen",  "besteht",
        "beträchtlich",  "bevor",  "bezüglich",  "bietet",  "bisher",  "bislang",  "bis",  "bleiben",
        "blieb",  "bloss",  "bloß",  "brachte",  "brachten",  "brauchen",  "braucht",  "bringen",  "bräuchte",
        "bzw",  "böden",  "ca.",  "dabei",  "dadurch",  "dafür",  "dagegen",  "dahin",  "damals",  "danach",
        "daneben",  "dank",  "danke",  "danken",  "dannen",  "daran",  "darauf",  "daraus",  "darf",  "darfst",
        "darin",  "darum",  "darunter",  "darüber",  "darüberhinaus",  "dass",  "davon",  "davor",  "demnach",
        "denen",  "dennoch",  "derart",  "derartig",  "derem",  "deren",  "derjenige",  "derjenigen",  "derzeit",
        "desto",  "deswegen",  "diejenige",  "diesseits",  "dinge",  "direkt",  "direkte",  "direkten",
        "direkter",  "doppelt",  "dorther",  "dorthin",  "drauf",  "drei",  "dreißig",  "drin",  "dritte",
        "drunter",  "drüber",  "dunklen",  "durchaus",  "durfte",  "durften",  "dürfen",  "dürfte",  "eben",
        "ebenfalls",  "ebenso",  "ehe",  "eher",  "eigenen",  "eigenes",  "eigentlich",  "einbaün",
        "einerseits",  "einfach",  "einführen",  "einführte",  "einführten",  "eingesetzt",  "einigermaßen",
        "eins",  "einseitig",  "einseitige",  "einseitigen",  "einseitiger",  "einst",  "einstmals",  "einzig",
        "ende",  "entsprechend",  "entweder",  "ergänze",  "ergänzen",  "ergänzte",  "ergänzten",  "erhalten",
        "erhielt",  "erhielten",  "erhält",  "erneut",  "erst",  "erste",  "ersten",  "erster",  "eröffne",
        "eröffnen",  "eröffnet",  "eröffnete",  "eröffnetes",  "etc",  "etliche",  "etwa",  "fall",  "falls",
        "fand",  "fast",  "ferner",  "finden",  "findest",  "findet",  "folgende",  "folgenden",  "folgender",
        "folgendes",  "folglich",  "fordern",  "fordert",  "forderte",  "forderten",  "fortsetzen",  "fortsetzt",
        "fortsetzte",  "fortsetzten",  "fragte",  "frau",  "frei",  "freie",  "freier",  "freies",  "fuer",
        "fünf",  "gab",  "ganzem",  "gar",  "gbr",  "geb",  "geben",  "geblieben",  "gebracht",  "gedurft",
        "geehrt",  "geehrte",  "geehrten",  "geehrter",  "gefallen",  "gefiel",  "gefälligst",  "gefällt",
        "gegeben",  "gehabt",  "gehen",  "geht",  "gekommen",  "gekonnt",  "gemocht",  "gemäss",  "genommen",
        "genug",  "gern",  "gestern",  "gestrige",  "getan",  "geteilt",  "geteilte",  "getragen",
        "gewissermaßen",  "geworden",  "ggf",  "gib",  "gibt",  "gleich",  "gleichwohl",  "gleichzeitig",
        "glücklicherweise",  "gmbh",  "gratulieren",  "gratuliert",  "gratulierte",  "gut",  "gute",  "guten",
        "gängig",  "gängige",  "gängigen",  "gängiger",  "gängiges",  "gänzlich",  "haette",  "halb",  "hallo",
        "hast",  "hattest",  "hattet",  "heraus",  "herein",  "heute",  "heutige",  "hiermit",  "hiesige",
        "hinein",  "hinten",  "hinterher",  "hoch",  "hundert",  "hätt",  "hätte",  "hätten",  "höchstens",
        "igitt",  "immer",  "immerhin",  "important",  "indessen",  "info",  "infolge",  "innen",  "innerhalb",
        "insofern",  "inzwischen",  "irgend",  "irgendeine",  "irgendwas",  "irgendwen",  "irgendwer",
        "irgendwie",  "irgendwo",  "je",  "jedenfalls",  "jederlei",  "jedoch",  "jemand",  "jenseits",
        "jährig",  "jährige",  "jährigen",  "jähriges",  "kam",  "kannst",  "kaum",  "keines",  "keinerlei",
        "keineswegs",  "klar",  "klare",  "klaren",  "klares",  "klein",  "kleinen",  "kleiner",  "kleines",
        "koennen",  "koennt",  "koennte",  "koennten",  "komme",  "kommen",  "kommt",  "konkret",  "konkrete",
        "konkreten",  "konkreter",  "konkretes",  "konnten",  "könn",  "könnt",  "könnten",  "künftig",  "lag",
        "lagen",  "langsam",  "lassen",  "laut",  "lediglich",  "leer",  "legen",  "legte",  "legten",  "leicht",
        "leider",  "lesen",  "letze",  "letzten",  "letztendlich",  "letztens",  "letztes",  "letztlich",
        "lichten",  "liegt",  "liest",  "links",  "längst",  "längstens",  "mag",  "magst",  "mal",
        "mancherorts",  "manchmal",  "mann",  "margin",  "mehr",  "mehrere",  "meist",  "meiste",  "meisten",
        "meta",  "mindestens",  "mithin",  "mochte",  "morgen",  "morgige",  "muessen",  "muesst",  "musst",
        "mussten",  "muß",  "mußt",  "möchte",  "möchten",  "möchtest",  "mögen",  "möglich",  "mögliche",
        "möglichen",  "möglicher",  "möglicherweise",  "müssen",  "müsste",  "müssten",  "müßte",  "nachdem",
        "nacher",  "nachhinein",  "nahm",  "natürlich",  "nacht",  "neben",  "nebenan",  "nehmen",  "nein",
        "neu",  "neue",  "neuem",  "neuen",  "neuer",  "neues",  "neun",  "nie",  "niemals",  "niemand",
        "nimm",  "nimmer",  "nimmt",  "nirgends",  "nirgendwo",  "nutzen",  "nutzt",  "nutzung",  "nächste",
        "nämlich",  "nötigenfalls",  "nützt",  "oben",  "oberhalb",  "obgleich",  "obschon",  "obwohl",  "oft",
        "per",  "pfui",  "plötzlich",  "pro",  "reagiere",  "reagieren",  "reagiert",  "reagierte",  "rechts",
        "regelmäßig",  "rief",  "rund",  "sang",  "sangen",  "schlechter",  "schließlich",  "schnell",  "schon",
        "schreibe",  "schreiben",  "schreibens",  "schreiber",  "schwierig",  "schätzen",  "schätzt",
        "schätzte",  "schätzten",  "sechs",  "sect",  "sehrwohl",  "sei",  "seit",  "seitdem",  "seite",
        "seiten",  "seither",  "selber",  "senke",  "senken",  "senkt",  "senkte",  "senkten",  "setzen",
        "setzt",  "setzte",  "setzten",  "sicherlich",  "sieben",  "siebte",  "siehe",  "sieht",  "singen",
        "singt",  "sobald",  "sodaß",  "soeben",  "sofern",  "sofort",  "sog",  "sogar",  "solange",  "solc",
        "hen",  "solch",  "sollen",  "sollst",  "sollt",  "sollten",  "solltest",  "somit",  "sonstwo",
        "sooft",  "soviel",  "soweit",  "sowie",  "sowohl",  "spielen",  "später",  "startet",  "startete",
        "starteten",  "statt",  "stattdessen",  "steht",  "steige",  "steigen",  "steigt",  "stets",  "stieg",
        "stiegen",  "such",  "suchen",  "sämtliche",  "tages",  "tat",  "tatsächlich",  "tatsächlichen",
        "tatsächlicher",  "tatsächliches",  "tausend",  "teile",  "teilen",  "teilte",  "teilten",  "titel",
        "total",  "trage",  "tragen",  "trotzdem",  "trug",  "trägt",  "toll",  "tun",  "tust",  "tut",  "txt",
        "tät",  "ueber",  "umso",  "unbedingt",  "ungefähr",  "unmöglich",  "unmögliche",  "unmöglichen",
        "unmöglicher",  "unnötig",  "unsem",  "unser",  "unsere",  "unserem",  "unseren",  "unserer",
        "unseres",  "unten",  "unterbrach",  "unterbrechen",  "unterhalb",  "unwichtig",  "usw",  "vergangen",
        "vergangene",  "vergangener",  "vergangenes",  "vermag",  "vermutlich",  "vermögen",  "verrate",
        "verraten",  "verriet",  "verrieten",  "version",  "versorge",  "versorgen",  "versorgt",  "versorgte",
        "versorgten",  "versorgtes",  "veröffentlichen",  "veröffentlicher",  "veröffentlicht",
        "veröffentlichte",  "veröffentlichten",  "veröffentlichtes",  "viele",  "vielen",  "vieler",  "vieles",
        "vielleicht",  "vielmals",  "vier",  "vollständig",  "voran",  "vorbei",  "vorgestern",  "vorher",
        "vorne",  "vorüber",  "völlig",  "während",  "wachen",  "waere",  "warum",  "weder",  "wegen",
        "weitere",  "weiterem",  "weiteren",  "weiterer",  "weiteres",  "weiterhin",  "weiß",  "wem",  "wen",
        "wenig",  "wenige",  "weniger",  "wenigstens",  "wenngleich",  "wer",  "werdet",  "weshalb",  "wessen",
        "weswegen",  "wichtig",  "wieso",  "wieviel",  "wiewohl",  "willst",  "wirklich",  "wodurch",  "wogegen",
        "woher",  "wohin",  "wohingegen",  "wohl",  "wohlweislich",  "womit",  "woraufhin",  "woraus",  "worin",
        "wurde",  "wurden",  "währenddessen",  "wär",  "wäre",  "wären",  "zahlreich",  "zehn",  "zeitweise",
        "ziehen",  "zieht",  "zog",  "zogen",  "zudem",  "zuerst",  "zufolge",  "zugleich",  "zuletzt",  "zumal",
        "zurück",  "zusammen",  "zuviel",  "zwanzig",  "zwei",  "zwölf",  "ähnlich",
        "übel",  "überall",  "überallhin",  "überdies",  "übermorgen",  "übrig",  "übrigens", "sodass", "über", "de"
    ];


    /**
     * TextAnalyzer constructor.
     *
     */
    public function __construct()
    {

    }


    /**
     * Sets the text for the text analyzer.
     *
     * @param $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }


    /**
     * Gets the current text used by the text analyzer.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }


    /**
     * Gets the number of chars in the text.
     *
     * @return int The number of chars
     */
    public function getTextLength()
    {
        $text = $this->getText();

        // remove chars which should be ignored for text count
        $text = str_replace($this->ignoreCharList, "", $text);

        $textLength = strlen($text);

        return $textLength;
    }


    /**
     * Gets the number of words in the text.
     *
     * @return int The number of words.
     */
    public function getWordCount()
    {
        $wordCount = str_word_count($this->getText(), 0, $this->wordCharList);

        return $wordCount;
    }



    public function getTermFrequency()
    {

    }


    public function getSentences()
    {
        $content = $this->getText();

        $abbreviations = [ 'ca.' ];

        foreach( $abbreviations as $abbreviation )
        {
            $content = str_replace( $abbreviation, sprintf( "X%sX", $abbreviation ), $content );
        }

        $sentences = preg_split('/(?<=[.?!;])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);

        // check for spaces within each sentence, remove sentence if no space has been found
        $sentences = array_filter( $sentences, function ( $sentence ) {
            // check sentence
            if( stristr( $sentence, " " ) !== false )
            {
                $sentenceWordCount = str_word_count( $sentence );

                if( $sentenceWordCount > 3 )
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                // no space has been found in sentence
                return false;
            }
        });

        foreach( $sentences as &$sentence )
        {
            foreach( $abbreviations as $abbreviation )
            {
                $sentence = str_replace( sprintf( "X%sX", $abbreviation ), $abbreviation, $sentence );
            }
        }

        return $sentences;
    }


    public function getKeywords()
    {
        $text = $this->getText();
        $wordCount = $this->getWordCount();

        $cleanedText = self::cleanText($text);

        // remove umlauts
        $cleanedText = str_replace( $this->specialCharList['Original'], $this->specialCharList['Replace'], $cleanedText );
        $cleanedText = $this->removeStopwordsFromText($cleanedText);

        // replace commas, hyphens, quotes etc (count as spaces)
//		$cleanedText = preg_replace('`[",:;()/\`]`', ' ', $cleanedText); // -

        // remove signs
        $cleanedText = str_replace($this->signs, ' ', $cleanedText);

        $wdfResult = $this->getWordData($cleanedText, $wordCount);

        return $wdfResult;
    }


    public function getNGrams($maxNGram = 3)
    {
        $content = $this->getText();
        $cleanedText = $content;

        $cleanedText = str_replace($this->signs, ' ', $cleanedText);

        // replace commas, hyphens, quotes etc (count as spaces)
        $cleanedText = preg_replace('`[",:;()/\`]`', ' ', $cleanedText); // -

        $cleanedText = $this->cleanText( $cleanedText );
        $cleanedText = trim( $cleanedText, "\t\n\r\0\x0B\xc2\xa0" );
        $cleanedText = strtoupper($cleanedText);

        $nGramList = [];
        $words = explode(" ", $cleanedText);
        $textLength = count($words);

        // trim all words
        $words = array_map( 'trim', $words );

        // determine trigrams
        for( $offset = 0; $offset < $textLength - 2; $offset++ )
        {
            // get array parts for current ngram with offset and ngram length (length = 3 by default)
            $nGramParts = array_slice( $words, $offset, $maxNGram );
            // merge slices to n-gram string
            $currentNGram = trim( implode( " ", $nGramParts ) );

            // check if n-gram is valid
            if( !empty( $currentNGram ) )
            {
                // get new n-gram frequency
                if( isset( $nGramList[$currentNGram] ) )
                {
                    $nGramFrequency = intval( $nGramList[$currentNGram] + 1 );
                }
                else
                {
                    $nGramFrequency = 1;
                }

                // increment counter for current ngram (count frequency)
                $nGramList[$currentNGram] = $nGramFrequency;
            }
        }

        return $nGramList;
    }


    public static function cleanText($text)
    {
        $cleanedText = trim( $text );

        // Curly quotes etc
        $cleanedText = str_replace( array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"), array("'", "'", '"', '"', '-', '--', '...'), $cleanedText );
        $cleanedText = str_replace( array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)), array("'", "'", '"', '"', '-', '--', '...'), $cleanedText );

        // Replace periods within numbers
        $cleanedText = preg_replace( '`([^0-9][0-9]+)\.([0-9]+[^0-9])`mis', '${1}0$2', $cleanedText );

        // Assume blank lines (i.e., paragraph breaks) end sentences (useful
        // for titles in plain text documents) and replace remaining new
        // lines with spaces
        $cleanedText = preg_replace( '`(\r\n|\n\r)`is', "\n", $cleanedText );
        $cleanedText = preg_replace( '`(\r|\n){2,}`is', ".\n\n", $cleanedText );
        $cleanedText = preg_replace( '`[ ]*(\n|\r\n|\r)[ ]*`', ' ', $cleanedText );

        // Replace commas, hyphens, quotes etc (count as spaces)
        $cleanedText = preg_replace( '`[",:;()/\`]`', ' ', $cleanedText ); // -

        // Unify terminators and spaces
        $cleanedText = trim($cleanedText, '. ') . '.'; // Add final terminator.
//        $cleanedText = preg_replace('`[\.!?]`', '.', $cleanedText); // Unify terminators
        $cleanedText = preg_replace( '`([\.\s]*\.[\.\s]*)`mis', '. ', $cleanedText ); // Merge terminators separated by whitespace.
        $cleanedText = preg_replace( '`[ ]+`', ' ', $cleanedText ); // Remove multiple spaces
        $cleanedText = preg_replace( '`([\.])[\. ]+`', '$1', $cleanedText ); // Check for duplicated terminators
        $cleanedText = trim( preg_replace( '`[ ]*([\.])`', '$1 ', $cleanedText ) ); // Pad sentence terminators

        // remove signs
        $signs = [ ',', '.', '!', '"', '?', '[', ']', '\n', '\r', '=', '»', '%', '*', '&', ' - ', '|', '^', '\xc2\xa0' ];
        $cleanedText = str_replace( $signs, ' ', $cleanedText );

        $cleanedText = trim( $cleanedText, "\t\n\r\0\x0B\xc2\xa0" );

        // replace multiple spaces
        $cleanedText = preg_replace( '/\s+/', ' ', $cleanedText );

        $cleanedText = trim( $cleanedText );

        return $cleanedText;
    }


    public function removeStopWordsFromText( $text )
    {
        // search for stop words ("|" is logical or) within word boundaries (\b) and remove them
        $filteredText =  preg_replace( '/\b(?:'.implode( '|', array_map( 'preg_quote', $this->stopWords ) ).')\b/i', "", $text );

        return $filteredText;
    }


    private function getWordData( $text, $wordCount )
    {
        $textWordCount = $wordCount;

        $text = strtoupper($text);
        $words = array_filter( explode( " ", $text ) );

        $wordHistogramTemp = array_count_values( $words );

        arsort( $wordHistogramTemp );

        $wordHistogram  = [];
        foreach( $wordHistogramTemp as $currentWord => $currentWordCount )
        {
            $wdfFactor = $this->getWdfFactor( $currentWordCount, $textWordCount );

            $wordHistogram[$currentWord] = [
                'wordCount' => $currentWordCount,
                'wdfFactor' => $wdfFactor
            ];
        }

        $wordData = [
            'wordText' => $text,
            'wordHistogram' => $wordHistogram,
            'words' => $words,
            'wordcount' => $textWordCount
        ];

        return $wordData;
    }


    private function getWdfFactor( $currentWordCount, $textWordCount )
    {
        // WDF 				-		Within Document Frequency
        // Freq(i, j) 		-		current word amount within document
        // L				-		text length within document
        // WDF = log2(Freq(i, j) + 1) / log2(L)

        if( $textWordCount > 1 )
        {
            $wdfFactor = floatval( log10($currentWordCount + 1) / log10($textWordCount));
        }
        else
        {
            // prevent division by zero
            $wdfFactor = 0;
        }

        return $wdfFactor;
    }
}