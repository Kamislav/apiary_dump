# apiary_dump

[![Build Status](https://travis-ci.org/Kamislav/apiary_dump.svg?branch=master)](https://travis-ci.org/Kamislav/apiary_dump)
[![Coverage Status](https://coveralls.io/repos/github/Kamislav/apiary_dump/badge.svg?branch=master)](https://coveralls.io/github/Kamislav/apiary_dump?branch=master)

> **DISCLAIMER:** This is not an official Apiary tool! Use it at your own risk. It might not work at all.

Apiary dump is an lightweight commandline tool to manipulate API documentation
stored on Apiary. It allows you to create, list, fetch and publish your API documentation.
It is written in PHP, so you can use the base class ApiaryDocClient() directly
(not wrapped in symphony console).

## Requirements

### required
- PHP > 5.0
- nategood/httpful PHP REST client library
- symphony/console PHP framework Component
- Apiary token

### optional
- CURL and PHP CURL module

## Installation

### Clone the repository

** HTTP **
> git clone https://github.com/Kamislav/apiary_dump.git

** ssh **
> git clone git@github.com:Kamislav/apiary_dump.git

### initialize and update composer

```shell
cd ./apiary_dump
composer init
composer update
```

## Usage

### CLI mode

```shell
php ./apiary_cli.php {command} [options] {ApiaryToken} {required parameters} [optional parameters]

# U can use tool help
php ./apiary_cli.php help {command}

# To get available commands Use
php ./apiary_cli.php list
```

### PHP library

```php
require_once("ApiaryDocClient.php");

/** @var \ApiaryDump\ApiaryDocClient **/
$client = new ApiaryDocClient("YOUR_TOKEN");
$response = $client->listApis();
```
## Development

- This code is maintained in KISS principle, but allways keep in mind, that you
  should write testable and readable code as much as possible and confortable.

- The first reason for the KISS (until realy need more) principle is that
  I don't like to write more complex code and more lines only because
  of it may or may not be needed in the future.

- Second point of view is that this tool should be kept as simple as possible,
  so more developers will be able to fork it, hack it to their needs and possible
  send an pull request with some new functioanlity.
