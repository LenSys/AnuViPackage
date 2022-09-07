<?php

namespace App\AnuVi;


use Doctrine\Instantiator\Exception\InvalidArgumentException;
use GuzzleHttp\Client;
use Cache;

/**
 * Class WebDataManager
 * @package App\AnuVi
 *
 */
class WebDataManager
{

    /**
     * Proxy URL is used to run a request via a proxy,
     * e.g. a search request for Google web results
     * done via proxy to prevent IP blockage.
     * TODO: enter a valid proxy URL.
     */
    const PROXY_URL = "https://proxy-url/";

    /**
     * Constructor.
     */
    public function __construct()
    {

    }


    /**
     * Gets the HTTP response for a specific URL.
     *
     * @param           $url
     * @param array     $requestData
     * @param bool|true $cacheResponse
     *
     * @return array|mixed
     */
    public static function getHttpResponse($url, $requestData = [], $cacheResponse = true, $requestHeaders = [])
    {
        if( empty( $url ) )
        {
            throw new InvalidArgumentException();
        }

        if( $cacheResponse != false )
        {
        	if( $cacheResponse == true )
        	{
	        	$cacheDuration = config( "cache.CacheDuration" );
        	}
        	else
        	{
	        	$cacheDuration = intval( $cacheResponse );
        	}

            // set cache duration
            $cacheKey = sprintf( "%s.%s", __METHOD__, md5( $url . var_export( $requestData, true ) ) );
            $responseData = Cache::remember( $cacheKey, $cacheDuration, function () use ( $url, $requestData, $requestHeaders )
            {
                return self::getClientResponse( $url, $requestData, $requestHeaders );
            } );
        }
        else
        {
            $responseData = self::getClientResponse( $url, $requestData, $requestHeaders );
        }

        return $responseData;
    }


    /**
     * Posts a HTTP request to a specific URL.
     *
     * @param       $url
     * @param array $requestData
     *
     * @return array
     */
    public static function postHttpResponse($url, $requestData = [])
    {
        if(empty($url))
        {
            throw new InvalidArgumentException();
        }

        // do not cache a POST response!
        $responseData = self::postClientResponse($url, $requestData);

        return $responseData;
    }


    /**
     * Gets the Google search query response using a server proxy.
     *
     * @param string $searchQuery
     *
     * @return array
     */
    public static function getSearchQueryResponse($searchQuery)
    {
        $searchQueryResponseData = [];

        // TODO: validate input

        // crawl Google Search results using proxy
        $jsonResponseData = self::getProxyResponse([
            'ProxyType' => 'SearchQuery',
            'QueryData' => $searchQuery,
        ], [
            'User-Agent' => 'iPhone',
            'Authorization' => 'Basic ' . base64_encode(env('ANUVI_PROXY_USER') . ":" . env('ANUVI_PROXY_PASSWORD'))
        ]);

        $responseData = json_decode($jsonResponseData['body'], true);
        $responseData = $responseData['responseData'];

        if(!empty($responseData))
        {
            // get top 30 web pages for search query
            $searchQueryResponseData = $responseData['url'];

            if (count($searchQueryResponseData) > 100)
            {
                // remove first url => Google Url
                unset( $searchQueryResponseData[0] );
            }
        }

        return $searchQueryResponseData;
    }


    public static function getProxyResponse($requestData, $requestHeaders = [])
    {
        $responseData = [];

        if(empty($requestData))
        {
            throw new InvalidArgumentException();
        }

        // TODO: validate input data
        // -> ProxyType: "SearchQuery"
        // -> QueryData: string

        // -> RequestHeaders

        // TODO: rewrite code part
        if($requestData['ProxyType'] == 'SearchQuery')
        {
            $requestUrl = sprintf("%s/%s", self::PROXY_URL, "api/v1.0/ProxyRequest/");

            $searchQueryData = "searchQuery=" . $requestData['QueryData'];
            // replace spaces with a plus char
            $searchQueryData = str_replace( " ", "+", $searchQueryData );

            $responseData = self::getHttpResponse($requestUrl, $searchQueryData, true, $requestHeaders);
        }
        elseif($requestData['ProxyType'] == 'EAN')
        {
            $requestUrl = sprintf("%s/%s", self::PROXY_URL, "api/v1.0/ProxyRequest/");

            $eanData = "ean=" . $requestData['EAN'];

            $responseData = self::getHttpResponse($requestUrl, $eanData, true, $requestHeaders);
        }

        return $responseData;
    }


    public static function getWebPageSourceCode($url, $requestHeaders = [])
    {
        if(empty($url))
        {
            throw new InvalidArgumentException();
        }

        $defaultRequestHeaders = [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/600.1.17 (KHTML, like Gecko) Version/7.1 Safari/537.85.10'
        ];

        $requestHeaders = array_merge($defaultRequestHeaders, $requestHeaders);

        // TODO: rewrite code

        $cacheKey = sprintf("%s.%s", __METHOD__, md5($url));
        $webPageSourceCode = Cache::remember($cacheKey, config("cache.CacheDuration"), function () use ($url, $requestHeaders)
        {
            $requestData = [];
            $responseData = self::getClientResponse($url, $requestData, $requestHeaders);

            // TODO: check status code

            $webPageSourceCode = $responseData['body'];

            return $webPageSourceCode;

        });

        return $webPageSourceCode;
    }


    private static function getClientResponse( $url, $requestData = [], $requestHeaders = [] )
    {
        // set user agent
        if( empty( $requestHeaders['User-Agent'] ) )
        {
            $userAgent = config( "app.name" ) . ' ' . config( "app.version" );

            $requestHeaders['User-Agent'] = $userAgent;
        }

        $useProxy = false;
        if( isset( $requestHeaders['UseProxy'] ) )
        {
	        $useProxy = true;
	        unset( $requestHeaders['UseProxy'] );
        }

        // create a new HTTP client with the header data
        $client = new Client( [
            'headers' => $requestHeaders
        ] );

        $responseData = [];

        try
        {
            $requestParameters = [];
            if( !empty( $requestData ) )
            {
                $requestParameters = [
                    'query' => $requestData
                ];
            }
            // dd($requestParameters);

            // run the HTTP request
            $response = $client->get( $url, $requestParameters );

            // extract the response data
            $responseData['statusCode'] = $response->getStatusCode();

            $responseData['headers'] = $response->getHeaders();

            $responseData['body'] = $response->getBody()->getContents();
        }
        catch(\Exception $e)
        {
             dd($e);
        }

        return $responseData;
    }


    private static function postClientResponse($url, $requestData)
    {
        $responseData = [];

        try
        {
            // create a new HTTP client with the header data
            $client = new Client([
                'headers' => [
                    'User-Agent' => config("app.name") . ' ' . config("app.version"),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);

            // run the HTTP request
            $response = $client->post($url, [
                    'form_params' => $requestData
                ]
            );

            // extract the response data
            $responseData['statusCode'] = $response->getStatusCode();

            $responseData['headers'] = $response->getHeaders();
            // 'application/json; charset=utf8'
            $responseData['body'] = $response->getBody()->getContents();
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }

        return $responseData;
    }
}
