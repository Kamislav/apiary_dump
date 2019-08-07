<?php
require_once(__DIR__ . "/../BaseTestClass.php");

use ApiaryDump\ApiaryDocClient;
use Httpful\Response;
use Httpful\Http;
use Httpful\Mime;

final class CreateTest extends BaseTestClass
{
protected $defaultParameretsArray = [];

  public function  setUp(): void {
    $this->defaultParameretsArray = [
          'type' => "personal",
          'public' => TRUE,
          'desiredName' => "utCreate",
          'code' => 'FORMAT: 1A' . "\n" . '# API',
        ];
  }

  public function testCanBeCreatedApiProject() {
//var_dump($this->requestMock);
    $validResponse = unserialize(file_get_contents(__DIR__ . "/fixtures/create_response"));
    $mock = $this->requestMock;
    $mock->shouldReceive("init")->andReturn($mock)
      ->shouldReceive("addHeader")->with("Content-Type", "application/json; charset=utf-8")->once()
      ->shouldReceive("addHeader")->with("Authentication", "Token valid_token")->once()
      ->shouldReceive("uri")->with("https://api.apiary.io/blueprint/create")->andReturn($mock)->once()
      ->shouldReceive("method")->with(Http::POST)->andReturn($mock)->once()
      ->shouldReceive("sendsType")->with(Mime::FORM)->andReturn($mock)->once()
      ->shouldReceive("body")
        ->with("type=personal&public=1&desiredName=utCreate&code=FORMAT%3A+1A%0A%23+API")
        ->andReturn($mock)->once()
      ->shouldReceive("send")->andReturn($validResponse)->once()
      ->andSet('additional_curl_opts', [CURLOPT_RETURNTRANSFER => "TRUE"])
    ;
    $client = new ApiaryDocClient("valid_token", "https://", "api.apiary.io", $mock);

    $data = json_encode($this->defaultParameretsArray);
    $response = $client->createApiProject($data);
//var_dump($response);

    $this->assertTrue(
      isset($response->code)
      && $response->code === 201
      && $response->body->status === "created"
    );
  }

  public function testCallWithInvalidToken() {
    $invalidResponse = unserialize(file_get_contents(__DIR__ . "/fixtures/createInvalidToken_response"));
    //var_dump($invalidResponse);
    $mock = $this->requestMock;
    $mock->shouldReceive("init")->andReturn($mock)
      ->shouldReceive("addHeader")->with("Content-Type", "application/json; charset=utf-8")->once()
      ->shouldReceive("addHeader")->with("Authentication", "Token invalid_token")->once()
      ->shouldReceive("uri")->with("https://api.apiary.io/blueprint/create")->andReturn($mock)->once()
      ->shouldReceive("method")->with(Http::POST)->andReturn($mock)->once()
      ->shouldReceive("sendsType")->with(Mime::FORM)->andReturn($mock)->once()
      ->shouldReceive("body")
        ->with("type=personal&public=1&desiredName=utCreate&code=FORMAT%3A+1A%0A%23+API")
        ->andReturn($mock)->once()
      ->shouldReceive("send")->andReturn($invalidResponse)->once()
      ->andSet('additional_curl_opts', [CURLOPT_RETURNTRANSFER => "TRUE"])
    ;
    $client = new ApiaryDocClient("invalid_token", "https://", "api.apiary.io", $mock);

    $data = json_encode($this->defaultParameretsArray);
    $response = $client->createApiProject($data);
//var_dump($response);

    $this->assertTrue(
      isset($response->code)
      && $response->code === 403
      && ! isset($response->body->status)
    );
  }

}
