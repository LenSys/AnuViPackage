<?php

namespace App\AnuVi;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use WebData;
use Log;

/**
 * Class WebDataManager
 * @package App\AnuVi
 *
 */
class WordPressManager
{

    /**
     * Constructor.
     */
    public function __construct()
    {

    }


    private static function createApiTokenData()
    {
        // create a hash for the API
        $apiAccessId = md5(microtime(true));
        $apiAccessToken = md5(env('ANUVI_API_TOKEN') . $apiAccessId);

        $tokenData = [
            'APIAccessId' => $apiAccessId,
            // create the calculated response for the API
            'APIAccessToken' => $apiAccessToken
        ];

        return $tokenData;
    }


    private static function getApiEndpointData($endpointUrl, $apiData)
    {
        if(empty($endpointUrl))
        {
            throw new InvalidArgumentException();
        }

        $tokenData = self::createApiTokenData();

        $apiData = array_merge($apiData, $tokenData);

        Log::info(__METHOD__ . ': ' . $endpointUrl . '(' . var_export($apiData, true) . ')');

        // get API endpoint and cache response
        $response = WebData::getHttpResponse($endpointUrl, $apiData, true);

        $responseData = [];

        // check for valid staus code
        if(isset($response['statusCode']) && ($response['statusCode'] == Response::HTTP_OK))
        {
            $responseData = $response['body'];
        }

        return $responseData;
    }


    public static function getTags($projectUrl)
    {
        if(empty($projectUrl))
        {
            throw new InvalidArgumentException();
        }

        $apiData = [];

        $endpointUrl = $projectUrl . "/wp-json/wp/v2/tag/";

        Log::info(__METHOD__ . ': ' . $endpointUrl);

        return self::getApiEndpointData($endpointUrl, $apiData);
    }



    public static function getCategories($projectUrl)
    {
        if(empty($projectUrl))
        {
            throw new InvalidArgumentException();
        }

        $apiData = [];

        $endpointUrl = $projectUrl . "/wp-json/wp/v2/categories/";

        Log::info(__METHOD__ . ': ' . $endpointUrl);

        return self::getApiEndpointData($endpointUrl, $apiData);
    }


    public static function getPosts($projectUrl)
    {
        if(empty($projectUrl))
        {
            throw new InvalidArgumentException();
        }

        $apiData = [];

        $endpointUrl = $projectUrl . "/wp-json/wp/v2/posts/";

        Log::info(__METHOD__ . ': ' . $endpointUrl);

        return self::getApiEndpointData($endpointUrl, $apiData);
    }


    public static function getPages($projectUrl)
    {
        if(empty($projectUrl))
        {
            throw new InvalidArgumentException();
        }

        $apiData = [];

        $endpointUrl = $projectUrl . "/wp-json/wp/v2/pages/";

        Log::info(__METHOD__ . ': ' . $endpointUrl);

        return self::getApiEndpointData($endpointUrl, $apiData);
    }


    public static function getSiteData($projectUrl)
    {
        if(empty($projectUrl))
        {
            throw new InvalidArgumentException();
        }

        $apiData = [];

        $endpointUrl = $projectUrl . '/wp-json/AVWPConfigurator/v1/sites/';

        Log::info(__METHOD__ . ': ' . $endpointUrl);

        return self::getApiEndpointData($endpointUrl, $apiData);
    }


    public static function hasPost($projectUrl, $postTitle)
    {
        if(empty($postTitle))
        {
            throw new InvalidArgumentException();
        }

        $apiData = [
            'PostTitle' => ($postTitle)
        ];

        $endpointUrl = $projectUrl . '/wp-json/AVWPConfigurator/v1/post/';

        Log::info(__METHOD__ . ': ' . $endpointUrl);

        return self::getApiEndpointData($endpointUrl, $apiData);
    }


    public static function getPostInfo($projectUrl, $postInfoId, $postSlug = "")
    {
        $apiData = [
            'PostId' => intval($postInfoId)
        ];

        if ( ! empty( $postSlug ))
        {
            $apiData['PostSlug'] = $postSlug;
        }

        $endpointUrl = $projectUrl . '/wp-json/AVWPConfigurator/v1/post/info';

        Log::info(__METHOD__ . ': ' . $endpointUrl);

        return self::getApiEndpointData($endpointUrl, $apiData);
    }


    public static function getPostsList( $projectUrl, $paged = 1, $authorId = -1 )
    {
        $apiData = [
            'paged' => intval($paged)
        ];

        if( $authorId > 0 )
        {
	        $apiData['AuthorId'] = intval( $authorId );
        }

        $endpointUrl = $projectUrl . '/wp-json/AVWPConfigurator/v1/posts/list';

        Log::info(__METHOD__ . ': ' . $endpointUrl);

        return self::getApiEndpointData($endpointUrl, $apiData);
    }


    public static function publishPost($postApiUrl, $requestData)
    {
        if(empty($postApiUrl))
        {
            throw new InvalidArgumentException();
        }

        if(empty($requestData))
        {
            throw new InvalidArgumentException();
        }

        $tokenData = self::createApiTokenData();

        $apiData = [
            'PostApiUrl' => $postApiUrl,
            'RequestData' => $requestData
        ];

        $apiData = array_merge($apiData, $tokenData);

        // set correct API url
        $requestUrl = sprintf("%s/wp-json/AVWPConfigurator/v1/post/", $postApiUrl);

        Log::info(__METHOD__ . ': ' . $requestUrl . '(' . var_export($apiData, true) . ')');

        $response = WebData::postHttpResponse($requestUrl, $apiData);

        $responseData = [];
        // check for valid status code
        if(isset($response['statusCode']) && ($response['statusCode'] == Response::HTTP_OK))
        {
            $responseData = $response['body'];
        }

        return $responseData;
    }


    public static function updatePost($postApiUrl, $requestData)
    {
        if(empty($postApiUrl))
        {
            throw new InvalidArgumentException();
        }

        if(empty($requestData))
        {
            throw new InvalidArgumentException();
        }

        $tokenData = self::createApiTokenData();

        $apiData = [
            'PostApiUrl' => $postApiUrl,
            'RequestData' => $requestData
        ];

        $apiData = array_merge($apiData, $tokenData);

        // set correct API url
        $requestUrl = sprintf("%s/wp-json/AVWPConfigurator/v1/update-post/", $postApiUrl);

        Log::info(__METHOD__ . ': ' . $requestUrl . '(' . var_export($apiData, true) . ')');

        $response = WebData::postHttpResponse($requestUrl, $apiData);

        $responseData = [];
        // check for valid status code
        if(isset($response['statusCode']) && ($response['statusCode'] == Response::HTTP_OK))
        {
            $responseData = $response['body'];
        }

        return $responseData;
    }


    public static function slugify( $text )
	  {
		  $text = str_replace( [ "ä", "ö", "ü", "ß", "Ä", "Ö", "Ü" ], [ "ae", "oe", "ue", "ss", "Ae", "Oe", "Ue" ], $text );

		  // replace non letter or digits by -
		  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

		  // transliterate
		  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		  // remove unwanted characters
		  $text = preg_replace('~[^-\w]+~', '', $text);

		  // trim
		  $text = trim($text, '-');

		  // remove duplicate -
		  $text = preg_replace('~-+~', '-', $text);

		  // lowercase
		  $text = strtolower($text);

		  if( empty( $text ) )
		  {
		    return 'n-a';
		  }

		  return $text;
	}


	public static function slugToTitle( $slug )
	{
		// set spaces instead of "-"
		$title = str_replace( "-", " ", $slug );

		// change umlauts
		$title = str_replace( ["ae", "oe", "ue"], ["ä", "ö", "ü"], $title );

		// convert first letter of every word to uppercase
		$title = ucwords( $title );

		$title = str_replace( [
			"Baürn",
			"Feür",
			"Iphone",
			"Ipad",
			"Pälla",
			"qüü",
			"Qür",
			"Qüen",
			" Für "
			], [
			"Bauern",
			"Feuer" ,
			"iPhone",
			"iPad",
			"Paella",
			"queue",
			"Quer",
			"Queen",
			" für "
			], $title );

		return $title;
	}
}
