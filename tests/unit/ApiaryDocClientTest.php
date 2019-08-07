<?php
require_once(__DIR__ . "/../BaseTestClass.php");

use ApiaryDump\ApiaryDocClient;
use Httpful\Request;
use Httpful\Response;
use Mockery\Mock;

final class ApiaryDocClientTest extends BaseTestClass
{
  /**
   * Mock objects SDO_DAS_DataFactory
   *
   * @param  Response $response Fake response object
   * @param  string $token    Security token
   *
   * @return Mockery\Mock           [description]
   */
  protected function getMockObject($response, $token, $requestMock) {
    /** @var Mockery\Mock ApiaryDocClient mock object */
    $mock = Mockery::mock('ApiaryDump\ApiaryDocClient[prepareRequest]', [$token]);
    $mock->shouldAllowMockingProtectedMethods()
      ->shouldReceive("prepareRequest")->andReturn($requestMock)
      ;

    return $mock;
  }

  public function testListTeamApis() {
    $response = new stdClass();
    $response->body = ["apis" => ["mockapi" => TRUE]];
    $requestMock = Mockery::mock('Request');
    $requestMock->shouldReceive('uri')->andReturn($requestMock)
      ->shouldReceive('method')->andReturn($requestMock)
      ->shouldReceive('send')->andReturn($response)
      ;
    /** @var Mockery\Mock Mock object */
    $partialMock = $this->getMockObject($response, 'valid_token', $requestMock);

    $response = $partialMock->listTeamApis("valid_team");
    //var_dump($response);

    $this->assertTrue(
      isset($response->body['apis'])
      && is_array($response->body['apis'])
      && $response->body['apis']['mockapi'] === TRUE
    );
  }

  public function testParseErrorResponse() {
    //tesr parsing error responses
    // ######## error type 1
    $response = new stdClass();
    $response->body = new stdClass();
    $response->body->error = TRUE;
    $requestMock = null;
    /** @var Mockery\Mock Mock object */
    $partialMock = $this->getMockObject($response, 'valid_token', $requestMock);
    $response = $partialMock->parseResponse($response);

    //var_dump($response);
    $this->assertTrue(
      $response === "1\n"
    );
    // ####### error type 2
    $response = new stdClass();
    $response->body = new stdClass();
    $response->body->error = FALSE;
    $response->body->message = "Mocked error mesage";
    $requestMock = null;
    /** @var Mockery\Mock Mock object */
    $partialMock = $this->getMockObject($response, 'valid_token', $requestMock);
    $response = $partialMock->parseResponse($response);

    //var_dump($response);
    $this->assertTrue(
      $response === " - Mocked error mesage\n"
    );
    // ####### error type 3
    $response = new stdClass();
    $response->body = new stdClass();
    $response->body->error = FALSE;
    $response->body->message = "";
    $response->code = 300;
    $requestMock = null;
    /** @var Mockery\Mock Mock object */
    $partialMock = $this->getMockObject($response, 'valid_token', $requestMock);
    $response = $partialMock->parseResponse($response);

    //var_dump($response);
    $this->assertTrue(
      $response === " -  - HTTP error code: 300\n"
    );
  }

  public function testParseJsResponse() {
    //tesr parsing JS responses
    $response = new stdClass();
    $response->body = new stdClass();
    $response->body->mockdata = true;
    $response->raw_body = '{"mockdata": true}';
    $requestMock = null;
    /** @var Mockery\Mock Mock object */
    $partialMock = $this->getMockObject($response, 'valid_token', $requestMock);
    $response = $partialMock->parseResponse($response);

    //var_dump($response);
    $this->assertTrue(
      $response === '{"mockdata":true}'
    );
  }

  public function testParseListResponse() {
    //tesr parsing List responses
    $response = new stdClass();
    $response->body = new stdClass();
    $response->body->apis = ["api1", "api2"];
    $requestMock = null;
    /** @var Mockery\Mock Mock object */
    $partialMock = $this->getMockObject($response, 'valid_token', $requestMock);
    $response = $partialMock->parseResponse($response);

    //var_dump($response);
    $this->assertTrue(
      is_array($response)
      && $response === ["api1", "api2"]
    );
  }

  public function testParseFetchResponse() {
    //tesr parsing Fetch responses
    $response = new stdClass();
    $response->body = new stdClass();
    $response->body->code = "mocked code";
    $response->body->error = FALSE;
    $response->body->message = "";
    $requestMock = null;
    /** @var Mockery\Mock Mock object */
    $partialMock = $this->getMockObject($response, 'valid_token', $requestMock);
    $response = $partialMock->parseResponse($response);

    //var_dump($response);
    $this->assertTrue(
      $response === "mocked code"
    );
  }

  public function testParsePublishResponse() {
    //tesr parsing Publish responses
    $response = new stdClass();
    $response->body = new stdClass();
    $response->body->code = "mocked code";
    $response->raw_body = '{}';
    $fetchResponse = new stdClass();
    $fetchResponse->body = new stdClass();
    $fetchResponse->body->code = "mocked code";
    $fetchResponse->body->error = FALSE;
    $fetchResponse->body->message = "";
    $requestMock = Mockery::mock('Request');
    $requestMock->shouldReceive('uri')->andReturn($requestMock)
      ->shouldReceive('method')->andReturn($requestMock)
      ->shouldReceive('send')->andReturn($fetchResponse)
      ;
    /** @var Mockery\Mock Mock object */
    $partialMock = $this->getMockObject($response, 'valid_token', $requestMock);
    $response = $partialMock->parseResponse($response);

    //var_dump($response);
    $this->assertTrue(
      is_array($response)
      && $response[''] === "mocked code"
    );

    $response = new stdClass();
    $response->body = new stdClass();
    $response->body->message = "mocked messag";
    $response->raw_body = '{}';
      /** @var Mockery\Mock Mock object */
      $partialMock = $this->getMockObject($response, 'valid_token', $requestMock);
      $response = $partialMock->parseResponse($response);

      //var_dump($response);
      $this->assertTrue($response === "No response or unrecognized format.");

  }

}
