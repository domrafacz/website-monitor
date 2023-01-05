<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\RequestRunnerResponseDto;
use App\Entity\Website;
use App\Service\RequestsRunnerResponseParser;
use App\Tests\Unit\Traits\WebsiteTrait;
use PHPUnit\Framework\TestCase;

class RequestsRunnerResponseParserTest extends TestCase
{
    use WebsiteTrait;
    private RequestRunnerResponseDto $response;
    private RequestsRunnerResponseParser $parser;
    protected function setUp(): void
    {
        $this->response = new RequestRunnerResponseDto();
        $this->parser = new RequestsRunnerResponseParser();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($response);
        unset($parser);
    }

    public function testParseResponseWithoutWebsite(): void
    {
        $this->response->website = null;
        $this->response = $this->parser->parse($this->response);

        $this->assertCount(1, $this->response->errors);
        $this->assertEquals('website is invalid', $this->response->errors[0]);
        $this->assertEquals(Website::STATUS_ERROR, $this->response->status);
    }

    public function testParseResponseUnexpectedHttpStatusCode(): void
    {
        $this->response->website = $this->createWebsite(10)->setExpectedStatusCode(500);
        $this->response->statusCode = 200;
        $this->response = $this->parser->parse($this->response);

        $this->assertCount(2, $this->response->errors);
        $this->assertEquals('request_runner_unexpected_http_code_simple', $this->response->errors[0]);
        $this->assertEquals(200, $this->response->errors[1]);
        $this->assertEquals(Website::STATUS_ERROR, $this->response->status);
    }

    public function testParseValidResponse(): void
    {
        $this->response->website = $this->createWebsite(10)->setExpectedStatusCode(200);
        $this->response->statusCode = 200;
        $this->response = $this->parser->parse($this->response);

        $this->assertCount(0, $this->response->errors);
        $this->assertEquals(Website::STATUS_OK, $this->response->status);
    }
}
