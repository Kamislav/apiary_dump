<?php
require_once(__DIR__ . "/../BaseTestClass.php");

use ApiaryDump\ApiaryDocClient;
use Httpful\Response;
use Httpful\Http;
use Httpful\Mime;

final class PublishTest extends BaseTestClass
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

  protected function getMockObject($response, $body, $token) {
    $mock = $this->requestMock;
    $mock->shouldReceive("init")->andReturn($mock)
      ->shouldReceive("addHeader")->with("Content-Type", "application/json; charset=utf-8")->once()
      ->shouldReceive("addHeader")->with("Authentication", "Token " . $token)->once()
      ->shouldReceive("uri")->with("https://api.apiary.io/blueprint/publish/utCreate")->andReturn($mock)->once()
      ->shouldReceive("method")->with(Http::POST)->andReturn($mock)->once()
      ->shouldReceive("sendsType")->with(Mime::FORM)->andReturn($mock)->once()
      ->shouldReceive("body")
        ->with($body)
        ->andReturn($mock)->once()
      ->shouldReceive("send")->andReturn($response)->once()
      ->andSet('additional_curl_opts', [CURLOPT_RETURNTRANSFER => "TRUE"])
    ;

    return $mock;
  }

  public function testCanBePublishedApiProject() {
//var_dump($this->requestMock);
    $validResponse = unserialize(file_get_contents(__DIR__ . "/fixtures/publish_response"));
    $serializedBody = unserialize(file_get_contents(__DIR__ . "/fixtures/publish_request"))->serialized_payload;
    $mock = $this->getMockObject($validResponse, $serializedBody, "valid_token");
    $client = new ApiaryDocClient("valid_token", "https://", "api.apiary.io", $mock);
    $response = $client->publishApiBlueprint($this->defaultParameretsArray['desiredName'], __DIR__ . "/fixtures/defaultDocData");
    //var_dump($response);

    $this->assertTrue(
      isset($response->code)
      && $response->code === 201
      && $response->raw_body == "{}"
    );
  }

  public function testPublishWithWrongToken() {
    //var_dump($this->requestMock);
    $invalidResponse = unserialize(file_get_contents(__DIR__ . "/fixtures/publishInvalidToken_response"));
    $serializedBody = unserialize(file_get_contents(__DIR__ . "/fixtures/publishInvalidToken_request"))->serialized_payload;
    //var_dump($serializedBody);
    $mock = $this->getMockObject($invalidResponse, $serializedBody, "invalid_token");
    $client = new ApiaryDocClient("invalid_token", "https://", "api.apiary.io", $mock);
    $response = $client->publishApiBlueprint($this->defaultParameretsArray['desiredName'], __DIR__ . "/fixtures/defaultDocData");
    //var_dump($response);

    $this->assertTrue(
      isset($response->code)
      && $response->code === 403
      && $response->meta_data['http_code'] === 403
    );
  }

  public function testPublishWithWrongName() {
    //var_dump($this->requestMock);
    $invalidResponse = unserialize(file_get_contents(__DIR__ . "/fixtures/publishInvalidName_response"));
    $serializedBody = unserialize(file_get_contents(__DIR__ . "/fixtures/publishInvalidName_request"))->serialized_payload;
    //var_dump($serializedBody);
    $mock = $this->getMockObject($invalidResponse, $serializedBody, "invalid_token");
    $client = new ApiaryDocClient("invalid_token", "https://", "api.apiary.io", $mock);
    $response = $client->publishApiBlueprint($this->defaultParameretsArray['desiredName'], __DIR__ . "/fixtures/defaultDocData");
    //var_dump($response);

    $this->assertTrue(
      isset($response->code)
      && $response->code === 404
      && $response->body->error === FALSE
      && strpos($response->body->message, "does not exist") !== FALSE
    );
  }

}
