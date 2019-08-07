<?php
namespace ApiaryDump;

use Httpful\Request;
use Httpful\Response;
use Httpful\Http;
use Httpful\Mime;
use \stdClass;

require_once __DIR__.'/../vendor/autoload.php';

/**
 * Class implements basic apiary functionality as a PHP/PHP-cli module/tool
 */
class ApiaryDocClient {

  /** @var string|null Request protocol (https://) **/
  protected $protocol = null;
  /** @var string|null Apiary base URL (api.apiary.io) **/
  protected $baseUrl = null;
  /** @var string|null Apiary security token **/
  protected $token = null;
  /** @var string|null Lagst published subdomain **/
  protected $lastPubApiSubdo;
  /** @var Request|null Httpful\Request object (optional - for UT purposes) **/
  protected $request = null;

  /**
   * Class constructor
   *
   * @param string $token Security token
   * @param string $protocol Request protocol
   * @param string $baseUrl Apiary base URL
   * @param Request|null $request Httpful\Request object
   *
   * @return void No return value
   */
  public function __construct($token, $protocol = "https://", $baseUrl = "api.apiary.io", $request=null) {
    $this->protocol = $protocol;
    $this->baseUrl = $baseUrl;
    $this->token = $token;
    $this->request = $request;
  }

  /**
   * Function lists all available API's for an security token.
   *
   * @return Httpful\Response Httpful\Response object
   */
  public function listApis() {
    $endpointUriPath = "/me/apis";
    $response = $this->callGetApi($endpointUriPath);

    //var_dump($response);
    return $response;
  }

  /**
   * Function lists all available API's for an team security token.
   *
   * @param  string  $teamId Apiary team ID.
   *
   * @return Httpful\Response         HttpfulResponse
   */
  public function listTeamApis($teamId) {
    $endpointUriPath = "/me/teams/" . $teamId . "/apis";
    $response = $this->callGetApi($endpointUriPath);

    return $response;
  }

  /**
   * Method fetch blueprints for passed $subdomainsArray
   *
   * @param string $apiSubdoArray JSON encoded array
   *
   * @return array Returns array of blueprints
   */
  public function fetchBlueprints($apiSubdoArray) {
    $endpointUriPath = "/blueprint/get";
    $subdomainsArray = json_decode($apiSubdoArray);
    //var_dump($subdomainsArray);
    $blueprints = [];
    foreach ($subdomainsArray as $apiSubdomain) {
      $response = $this->callGetApi($endpointUriPath . "/" . $apiSubdomain, "Token");
      $parsed = $this->parseResponse($response);
      //var_dump($response, $parsed);
      $blueprints[$apiSubdomain] = $parsed;
    }

    return $blueprints;
  }

  /**
   * Method for creating Apiary projects.
   *
   * @param string $postfieldsJson JSON encoded parameters array
   *
   * @return Httpful\Response  Returns Httpful\Response object
   */
  public function createApiProject($postfieldsJson) {
    $endpointUriPath = "/blueprint/create";
    $postfieldsArray = json_decode($postfieldsJson, TRUE);

    $response = $this->callPostApi($endpointUriPath, $postfieldsArray, "Token");

    return $response;
  }

  /**
   * Method for publishing API blueprint or swagger code.
   *
   * @param string $apiSubdomain Subdomain to publish
   * @param string $docFile      Path to doc file
   *
   * @return Httpful\Response Returns Httpful\Response object
   */
  public function publishApiBlueprint($apiSubdomain, $docFile) {
    $this->lastPubApiSubdo = $apiSubdomain;

    $endpointUriPath = "/blueprint/publish/" . $apiSubdomain;
    $docfileContent = file_get_contents($docFile);
    $docfileObject = new stdClass();
    $docfileObject->code = $docfileContent;

    $response = $this->callPostApi($endpointUriPath, $docfileObject);
    //var_dump($response);

    return $response;
  }

  /**
   * Method for identifing and parsing different types of Apiary API responses
   *
   * @param Httpful\Response $response Httpful\Response object to parse
   *
   * @return array|stdClass|Httpful\Response Returns parsed response
   */
  public function parseResponse($response) {
    //var_dump(':c:' . $response . ":c:");
    $parsedResponse = "No response or unrecognized format.";

    // Be careful with the if ordering !
    if ($this->isErrorResponse($response)) {
      //var_dump("Error response");
      $parsedResponse = "Unknown error";
      if (isset($response->body->error)) {
        $parsedResponse = $response->body->error;
      }
      if (isset($response->body->message)) {
        $parsedResponse .= " - " . $response->body->message;
      }
      if (isset($response->code)) {
         $parsedResponse .= " - HTTP error code: ". $response->code;
      }
      $parsedResponse .=  "\n";
    } else if ($this->isJsResponse($response)) {
      //var_dump("JS response");
      $parsedResponse = json_encode($response->body);
    } else if ($this->isListResponse($response)) {
      //var_dump("List response");
      $parsedResponse = $response->body->apis;
    } else if ($this->isFetchResponse($response)) {
      //var_dump("Fetch response");
      $parsedResponse = $response->body->code;
    } else if ($this->isPublishResponse($response)) {
      //var_dump("Publish response");
      $parsedResponse = $this->fetchBlueprints(json_encode([$this->lastPubApiSubdo]));
      //var_dump($this->lastPubApiSubdo, $parsedResponse);
    }

    return $parsedResponse;
  }

  /**
   * Method for detecting Publish responses
   *
   * @param Httpful\Response $response Httpful\Response object
   *
   * @return boolean Returns true if response is Publish type
   */
  protected function isPublishResponse($response) {
    if (isset($response->body)
      && ! isset($response->body->error)
      && ! isset($response->body->message)
      && '{}' === $response->raw_body
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Method for detecting Error responses
   *
   * @param Httpful\Response $response Httpful\Response object
   *
   * @return boolean Returns true if response is Error type
   */
  protected function isErrorResponse($response) {
    if (
      (
        isset($response->body) && isset($response->body->error)
        && $response->body->error !== FALSE
        )
      ||
      (
        isset($response->body)
        && isset($response->body->error) && $response->body->error === FALSE
        && isset($response->body->message) && $response->body->message != ""
        )
      ||
      (
        isset($response->code)
        && $response->code >= 300
        )
    ) {
      return TRUE;
    }

    return FALSE;
  }

/**
 * Method for detecting JS responses
 *
 * @param Httpful\Response $response Httpful\Response object
 *
 * @return boolean Returns true if response is JS type
 */
protected function isJsResponse($response) {
    //var_dump($response);
    if (isset($response->body)
      && ! isset($response->body->error)
      && ! isset($response->body->apis)
      && ! isset($response->body->code)
      && '{}' !== $response->raw_body
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Method for detecting List responses
   *
   * @param Httpful\Response $response Httpful\Response object
   *
   * @return boolean Returns true if response is List type
   */
  protected function isListResponse($response) {
    if (isset($response->body)
      && isset($response->body->apis)
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Method for detecting Fetch responses
   *
   * @param Httpful\Response $response Httpful\Response object
   *
   * @return boolean Returns true if response is Fetch type
   */
  protected function isFetchResponse($response) {
    if (isset($response->body)
      && isset($response->body->code)
      && isset($response->body->error) && $response->body->error === FALSE
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Method for preparing common request parameters
   *
   * @param string $mode Switch between Bearer/Token modes
   *
   * @return Httpful\Request Returns request object prepared with common settings.
   */
  protected function prepareRequest($mode = "Bearer") {
    /** @var Request request object **/
    $request = Request::init();
    if ($this->request !== null) {
      $request = $this->request;
    }
    $request->addHeader("Content-Type", "application/json; charset=utf-8");
    $request->additional_curl_opts = [
      CURLOPT_RETURNTRANSFER => "TRUE",
    ];
    switch ($mode) {
      case "Bearer":
        $request->addHeader("Authorization", $mode . " " . $this->token);
        break;
      case "Token":
        $request->addHeader("Authentication", $mode . " " . $this->token);
        break;
    }

    return $request;
  }

  /**
   * Method for calling API by HTTP->GET
   *
   * @param string $endpointUriPath Endpoint PATH segment of URI
   * @param string $mode            Authentication/Authorization type {Bearer|Token}.
   *
   * @return Httpful\Response Returns Httpful\Response object
   */
  protected function callGetApi($endpointUriPath, $mode = "Bearer") {
    $request = $this->prepareRequest($mode);
    $uri = $this->protocol . $this->baseUrl . $endpointUriPath;

    $request->uri($uri)
    ->method(Http::GET);
    $response = $request->send();

    /* use for saving test fixtures
    $basename = "dummyName"; // change as needed
    $this->writeFixtures("." . $basename, $request, $response);
    */

    //var_dump($request,$response);

    return $response;
  }

  /**
   * Method for calling API by HTTP->POST
   *
   * @param string $endpointUriPath Endpoint PATH segment of URI
   * @param array $postfieldsArray Array of POST form fields.
   * @param string $mode            Authentication/Authorization type {Bearer|Token}
   *
   * @return Httpful\Response Returns Httpful\Response object.
   */
  protected function callPostApi($endpointUriPath, $postfieldsArray, $mode = "Token") {
    $request = $this->prepareRequest($mode);
    $uri = $this->protocol . $this->baseUrl . $endpointUriPath;

    $request->uri($uri)
    ->method(Http::POST)
    ->sendsType(Mime::FORM)
    ->body(http_build_query($postfieldsArray));

    $response = $request->send();

    /* use for saving test fixtures
    $basename = "test";
    $this->writeFixtures($basename, $request, $response);
    */

//var_dump($request,$response);

    return $response;
  }

// @codeCoverageIgnoreStart
  /**
   * Save fixtures for testing purposes on disk
   *
   * @param string $basename Base of filename to be saved.
   * @param Httpful\Request $request  Httpful request object
   * @param Httpful\Response $response Httpful response object
   *
   * @return void No return value
   */
  protected function writeFixtures($basename, $request, $response) {
    $index = 1;
    $origBasename = "fixture_" . $basename;
    while(file_exists($basename . '_request') || file_exists($basename . '_response')) {
      $basename = $origBasename . "_" . $index;
      $index++;
    }
    file_put_contents($basename . "_request", serialize($request));
    file_put_contents($basename . "_response", serialize($response));
    $strippedResponse = clone $response;
    if (is_object($strippedResponse)) {
      $strippedResponse->body = "<STRIPPED>";
      $strippedResponse->raw_body = "<STRIPPED>";
      $strippedResponse->raw_headers = "<STRIPPED>";
      $strippedResponse->request->raw_headers = "<STRIPPED>";
      $strippedResponse->serialized_payload = "{'STRIPPED': true}";
      //var_dump($strippedResponse);
      file_put_contents($basename . "_stripped_response", serialize($strippedResponse));
    }
  }

  /**
   * Method returns last segment of pased PATH
   *
   * @param string $uriPath URI PATH segment to parse
   *
   * @return string Returns last segment of URI PATH
   */
  protected function getLastSegment($uriPath) {
    $slashPos = strrpos($uriPath,"/");
    if ($slashPos ===  strlen($uriPath) - 1) {
      $uriPath = substr($uriPath, 0, $slashPos - 1);
      $slashPos = strrpos($uriPath,"/");
    }
    $lastSegment = substr($uriPath, $slashPos);

    return $lastSegment;
  }
  // @codeCoverageIgnoreEnd

}
