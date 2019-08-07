<?php
require_once(__DIR__ . "/../BaseTestClass.php");

use ApiaryDump\ApiaryDocClient;
use Httpful\Response;
use Httpful\Http;
use Httpful\Mime;

final class FetchTest extends BaseTestClass
{
  protected function getMockObject($response, $token, $name = "utcreate") {
    $mock = $this->requestMock;
    $mock->shouldReceive("init")->andReturn($mock)
    ->shouldReceive("addHeader")->with('Content-Type', 'application/json; charset=utf-8')->once()
    ->shouldReceive("addHeader")->with('Authentication', 'Token ' . $token)->once()
    ->shouldReceive("uri")->with("https://api.apiary.io/blueprint/get/" . $name)->andReturn($mock)->once()
    ->shouldReceive("method")->with("GET")->andReturn($mock)->once()
    ;
    if (is_array($response)) {
      $mock->shouldReceive("send")->andReturn($response[0],$response[1]);
    } else {
      $mock->shouldReceive("send")->andReturn($response);
    }

    return $mock;
  }

  protected function getMultiFetchMockObject($response, $token, $name = "utcreate") {
    $mock = $this->getMockObject($response, $token, $name[0]);
    $mock->shouldReceive("uri")->with("https://api.apiary.io/blueprint/get/" . $name[1])->andReturn($mock)->once();

    return $mock;
  }

  public function testFetchApiProject() {
    //var_dump($this->requestMock);
    $validResponse = unserialize(file_get_contents(__DIR__ . "/fixtures/fetchApiDoc_response"));
    $mock = $this->getMockObject($validResponse, "valid_token");
    $client = new ApiaryDocClient("valid_token", "https://", "api.apiary.io", $mock);
    $response = $client->fetchBlueprints('["utcreate"]');
    //var_dump($response);

    $this->assertTrue(
      is_array($response)
      && isset($response["utcreate"])
      && $response["utcreate"] === "FORMAT: 1A\n# utCreate"
    );
  }

  public function testFetchWithInvalidToken() {
    $invalidResponse = unserialize(file_get_contents(__DIR__ . "/fixtures/fetchInvalidToken_response"));
    $mock = $this->getMockObject($invalidResponse, "invalid_token");
    $client = new ApiaryDocClient("invalid_token", "https://", "api.apiary.io", $mock);
    $response = $client->fetchBlueprints('["utcreate"]');
    //var_dump($response);

    $this->assertTrue(
      is_array($response)
      && isset($response["utcreate"])
      && $response["utcreate"] === "Unknown error - HTTP error code: 403\n"
    );
  }

  public function testFetchWithInvalidName() {
    $invalidResponse = unserialize(file_get_contents(__DIR__ . "/fixtures/fetchInvalidName_response"));
    $mock = $this->getMockObject($invalidResponse, "valid_token", "invalid_name");
    $client = new ApiaryDocClient("valid_token", "https://", "api.apiary.io", $mock);
    $response = $client->fetchBlueprints('["invalid_name"]');
    //var_dump($response);

    $this->assertTrue(
      is_array($response)
      && isset($response["invalid_name"])
      && $response["invalid_name"] === "1 - Suite invalid_name does not exist - HTTP error code: 404\n"
    );
  }

  public function testFetchWithMultipleNames() {
    $responses = [
      unserialize(file_get_contents(__DIR__ . "/fixtures/fetchMultipleNames_response")),
      unserialize(file_get_contents(__DIR__ . "/fixtures/fetchMultipleNames2_response"))
    ];
    $mock = $this->getMultiFetchMockObject($responses, "valid_token", ["utcreate","utcreatemore"]);
    //$mock = $this->getMultiFetchMockObject($responses, "valid_token", ["utcreate","utcreatemore"]);
    $client = new ApiaryDocClient("valid_token", "https://", "api.apiary.io", $mock);
    $response = $client->fetchBlueprints('["utcreate","utcreatemore"]');
    //var_dump("TEST RESPONSE",$response,"TEST RESPONSE END");

    $this->assertTrue(
      is_array($response)
      && count($response) === 2
      && isset($response["utcreate"])
      && $response["utcreate"] === "FORMAT: 1A\n# utCreate"
      && isset($response["utcreatemore"])
      && $response["utcreatemore"] === "FORMAT: 1A\n# utCreateMore"
    );
  }

}
