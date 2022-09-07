<?php

namespace App\AnuVi;

// aens: use Http client
use App\ProjectBacklink;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

use App\Project;

/**
 * Class GoogleManager
 * @package App\AnuVi
 *
 */
class GoogleManager
{
	// Google Search Console API documentation:
	// https://developers.google.com/apis-explorer/?hl=de#p/webmasters/v3/
	// https://developers.google.com/webmaster-tools/v3/errors?hl=de

	/**
	 * @var array
	 */
	private $endpoints = [
        'GoogleTokenURI' => 'https://accounts.google.com/o/oauth2/token',
		'GoogleSearchAPI' => 'https://www.googleapis.com/webmasters/v3',
		'GoogleAnalyticsAPI' => ''
	];

	/**
     * Constructor.
	 */
	public function __construct()
	{

	}


	/**
	 * Requests a new access token from the Google API endpoint using the current refresh token.
	 * (Refresh token do not expire!)
	 *
	 * @param $refreshToken
	 *
	 * @return bool|string
	 */
	public function updateAccessToken( $refreshToken )
    {
        // check for valid refresh token
        if (empty( $refreshToken )) {
            throw new InvalidArgumentException;
        }

        /*
         Each access token is only valid for a short time. Once the current access token expires,
        the server will need to use the refresh token to get a new one. To do this, send a POST
        request to https://accounts.google.com/o/oauth2/token with the following fields set:

        grant_type=refresh_token
        client_id=<the client ID token created in the APIs Console>
        client_secret=<the client secret corresponding to the client ID>
        refresh_token=<the refresh token from the previous step>
         */

        $accessToken = "";

        $googleApiEndPoint = sprintf( "%s", $this->endpoints['GoogleTokenURI'] );

        // get Google service information
        $providerData = config('services.google');

        if (empty( $providerData ))
        {
            throw new InvalidArgumentException();
        }

		// get a new access token by using the refresh token on Google Token URI endpoint
        $tokenRequestData = http_build_query([
					'grant_type' => 'refresh_token',
					'client_id' => $providerData['client_id'],
					'client_secret' => $providerData['client_secret'],
					'refresh_token' => $refreshToken
				]);

        // create a new HTTP client with the header data
        $client = new Client([
            'headers'     => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'User-Agent' => 'WebTool'
            ]
        ]);

        // run the HTTP request
        $response = $client->post($googleApiEndPoint,
            [
                'body' => $tokenRequestData
            ]
        );

        /*
         {
        access_token: "ya29.FgKj_D-..."
        token_type: "Bearer"
        expires_in: 3600
        id_token: "ey..."
        }
         */

        // extract the response data
        $responseData = [];
        $responseData['statusCode'] = $response->getStatusCode();

        // TODO: check status code

        // "200"
        $responseData['headers'] = $response->getHeaders();
        // 'application/json; charset=utf8'
        $responseData['body'] = json_decode($response->getBody(), true);

        $accessToken = $responseData['body'];

        return $responseData;

		return $accessToken;
	}


	/**
	 * Gets the search data from the Google API endpoint.
	 *
	 * @param $accessToken
	 * @param $domain
	 * @param $queryStartDate
	 * @param $queryEndDate
	 *
	 * @param string $dimension ('query' | 'page')
	 *
	 * @return array|bool
	 */
	public function getGoogleSearchData($accessToken, $domain, $queryStartDate, $queryEndDate, $dimension = 'query' )
	{
		// check for valid user token
		if( empty( $accessToken ) )
		{
			throw new InvalidArgumentException;
		}

		// check for valid domain
		if( empty( $domain ) )
		{
			throw new InvalidArgumentException;
		}

		// check for valid start date
		if( empty( $queryStartDate ) )
		{
			throw new InvalidArgumentException;
		}

		// check for valid end date
		if( empty( $queryEndDate ) )
		{
			throw new InvalidArgumentException;
		}

		// request data from Google Search API
		$googleApiEndPoint = sprintf( "%s/sites/%s/searchAnalytics/query?fields=rows", $this->endpoints['GoogleSearchAPI'], $domain );

		// generate request data for the query
		// => start and end date should be the same to get daily statistics
		$queryRequestData = json_encode([
			'startDate' => $queryStartDate,
			'endDate' => $queryEndDate,
			'dimensions' => [$dimension]
		]);

		// create a new HTTP client with the header data
		$client = new Client([
			'headers'     => [
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
				'Authorization' => 'Bearer ' . $accessToken,
				'User-Agent' => 'WebTool'
			]
		]);

		// run the HTTP request
		$response = $client->post($googleApiEndPoint,
			[
				'body' => $queryRequestData
			]
		);

		// extract the response data
		$responseData = [];
		$responseData['statusCode'] = $response->getStatusCode();

        // TODO: check status code

		// "200"
		$responseData['headers'] = $response->getHeaders();
		// 'application/json; charset=utf8'
		$responseData['body'] = json_decode($response->getBody(), true);

        $queryData = $responseData['body'];

		return $queryData;
	}


    /**
     *
     */
    public function importBacklinkData()
    {
        // get all CSV files in storage folder
        $backlinkFiles = glob(sprintf("%s/backlinks/*.csv", config("filesystems.disks.local.root")));

        foreach($backlinkFiles as $backlinkFile)
        {
            // ignore path to current file
            $backlinkFilename = basename($backlinkFile);

            // check if file has content
            if(filesize($backlinkFile) > 0)
            {
                // $backlinkData = array_map('str_getcsv', file($backlinkFile));
                $backlinkData = $this->loadCsvFile($backlinkFile);

                // check if backlink data is available
                if ($backlinkData)
                {
                    $hasDomainMatches = preg_match("/(?'Domain'.*)_([0-9])*/Ui", $backlinkFilename, $domainMatches);

                    if ($hasDomainMatches)
                    {
                        // try to find a project matching the current backlink domain

                        // replace dashes within domain because a dot is also represented as a dash
                        // e.g.:
                        //        'domain-de' represents the domain "Domain.de"
                        //        so the search string for this domain will be
                        //        'domain%de'
                        //
                        $domainSearchString = $domainMatches['Domain'];

                        // remove www from domain
						$domainSearchString = preg_replace( '/^www-/', '', $domainSearchString );

						// replace last '-' with a '.' (e.g. TLD '-de' -> '.de')
						$domainSearchString = preg_replace( '/-([A-Za-z]*)$/', '.$1', $domainSearchString );

						$projectDomain = strtolower( $domainSearchString );

                        // ignore first row (headers)
                        unset( $backlinkData[0] );

                        foreach($backlinkData as $currentBacklinkData)
                        {
                        	echo $projectDomain . "<br>";

                            try
                            {
                                $backlinkUrl           = htmlspecialchars($currentBacklinkData[0]);
                                $backlinkDetectionDate = $currentBacklinkData[1];

                                $backlinkUrlParts = parse_url( $backlinkUrl );
                                $backlinkDomain   = (string)$backlinkUrlParts['host'];

                                // remove www from domain
								$backlinkDomain = preg_replace( '/^www./', '', $backlinkDomain );

                                $projectBacklinkData = [
                                    'ProjectDomain'     => $projectDomain,
                                    'ProjectDomainHash' => crc32( strtolower( $projectDomain ) ),
                                    'BacklinkDomain'     => $backlinkDomain,
                                    'BacklinkDomainHash' => crc32( strtolower( $backlinkDomain ) ),
                                    'BacklinkUrl'        => $backlinkUrl,
                                    'BacklinkUrlHash'    => crc32( strtolower( $backlinkUrl ) ),
                                    'DetectionDate'      => $backlinkDetectionDate
                                ];

                                // import backlinks for project
                                ProjectBacklink::firstOrCreate( $projectBacklinkData );

                                //dd($projectBacklinkData);
                            }
                            catch (Exception $e)
                            {
                                var_export($e);
                            }
                        } // end foreach
                    } // end if ($hasDomainMatches)
                }
            }
        }
    }


    /**
     * Helper function to load the content of a CSV file to an array.
     *
     * @param $file
     *
     * @return array
     */
    private function loadCsvFile($file)
    {
        $fileData = array_map('str_getcsv', file($file));

        return $fileData;
    }


    public function uploadFile()
    {
        $file = Request::file('filefield');
        $extension = $file->getClientOriginalExtension();
        Storage::disk('local')->put($file->getFilename().'.'.$extension,  File::get($file));
    }
}
