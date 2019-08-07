<?php
require_once __DIR__.'/../src/ApiaryDocClient.php';

use PHPUnit\Framework\TestCase;
use ApiaryDump\ApiaryDocClient;
use Httpful\Request;

class BaseTestClass extends TestCase
{
  protected $requestMock = null;
  protected $apiaryMockUri = [
    "protocol" => "http://",
    "baseUrl" => "private-9e6eb-kamikapiarymock.apiary-mock.com"
    ];

  public function __construct() {
    parent::__construct();

    /** @var Mockery\mock object **/
    $mock = Mockery::mock("Request");
    $this->requestMock = $mock;
  }

  public function  setUp(): void {
    $mock = $this->requestMock;
    $mock->shouldIgnoreMissing()->asUndefined();
  }

  public function tearDown(): void {
    Mockery::close();
  }


}
