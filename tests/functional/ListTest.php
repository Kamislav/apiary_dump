<?php
require_once(__DIR__ . "/../BaseTestClass.php");

use ApiaryDump\ApiaryDocClient;
use Httpful\Response;
use Httpful\Http;
use Httpful\Mime;

final class ListTest extends BaseTestClass
{
  protected function getMockObject($response, $token) {
    $mock = $this->requestMock;
    $mock->shouldReceive("init")->andReturn($mock)->once()
      ->shouldReceive("addHeader")->with("")->andReturn($mock)->once()
      ->shouldReceive("uri")->with("https://api.apiary.io/me/apis")->andReturn($mock)->once()
      ->shouldReceive("send")->andReturn($response);
    ;

    return $mock;
  }

  public function testCanListPersonalApis() {
    //var_dump($this->requestMock);
    $token = "valid_token";
    $validResponse = unserialize(file_get_contents(__DIR__ . "/fixtures/listWithValidToken_response"));
    $mock = $this->getMockObject($validResponse, $token);
    $client = new ApiaryDocClient($token, "https://", "api.apiary.io", $mock);
    $response = $client->listApis($token);
    //var_dump($response);

    $this->assertTrue(
      isset($response->body)
      && isset($response->body->apis) && is_array($response->body->apis)
      && count($response->body->apis) === 3
      && isset($response->code) && $response->code === 200
    );
  }

  public function testListWithInvalidToken() {
    //var_dump($this->requestMock);
    $token = "invalid_token";
    $validResponse = unserialize(file_get_contents(__DIR__ . "/fixtures/listWithInvalidToken_response"));
    $mock = $this->getMockObject($validResponse, $token);
    $client = new ApiaryDocClient($token, "https://", "api.apiary.io", $mock);
    $response = $client->listApis($token);
    //var_dump($response);

    $this->assertTrue(
      isset($response->body)
      && isset($response->body->error) && $response->body->error === "Token Invalid"
      && isset($response->code) && $response->code === 401
    );
  }

}
