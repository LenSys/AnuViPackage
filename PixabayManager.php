<?php

namespace App\AnuVi;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use WebData;
use Log;

/**
 * Class PixabayManager
 * @package App\AnuVi
 *
 */
class PixabayManager
{

    /**
     * Constructor.
     */
    public function __construct()
    {

    }
	
	public static function searchImages( $searchQuery )
	{
		$endpointUrl = "https://pixabay.com/api/";
		
		$apiData = [
			'key' => env( 'PIXABAY_API_KEY' ),
			'q' => $searchQuery,
			'lang' => 'de',
			'image_type' => 'photo',
			'orientation' => 'horizontal',
			'per_page' => '200'
		];
		
		$response = WebData::getHttpResponse( $endpointUrl, $apiData, 24 * 60 );

        $responseData = [];

        // check for valid staus code
        if( isset( $response['statusCode'] ) && ( $response['statusCode'] == Response::HTTP_OK ) )
        {
            $responseData = $response['body'];
        }
        
        return $responseData;
	}
}