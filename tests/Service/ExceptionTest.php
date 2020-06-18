<?php declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service;

use Gam6itko\OzonSeller\Exception\AbstractOzonSellerException;
use Gam6itko\OzonSeller\Exception\AccessDeniedException;
use Gam6itko\OzonSeller\Exception\BadRequestException;
use Gam6itko\OzonSeller\Exception\NotFoundException;
use Gam6itko\OzonSeller\Service\V2\Posting\FbsService;
use Http\Client\Exception\HttpException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ExceptionTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return FbsService::class;
    }

    /**
     * @dataProvider dataProvider
     */
    public function test(string $class, string $json, array $expectedData = []): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->expects(self::once())
            ->method('getContents')
            ->willReturn($json);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($stream);

        $exception = $this->createMock(HttpException::class);
        $exception
            ->expects(self::once())
            ->method('getResponse')
            ->willReturn($response);

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects(self::once())
            ->method('sendRequest')
            ->willThrowException($exception);

        try {
            /** @var FbsService $svc */
            $svc = $this->createSvc($client);
            $svc->get('');
        } catch (AbstractOzonSellerException $exc) {
            self::assertInstanceOf($class, $exc);
            self::assertEquals($expectedData, $exc->getData());
            self::assertTrue(false !== strpos((string) $exc, 'Data:'));
        }
    }

    public function dataProvider()
    {
        yield [
            AccessDeniedException::class,
            '{"error":{"code":"ACCESS_DENIED","message":"Invalid Api-Key, please contact support","data":[]}}',
        ];

        yield [
            NotFoundException::class,
            '{"error":{"code":"NOT_FOUND_ERROR","message":"POSTING_NOT_FOUND","data":[]}}',
        ];

        yield [
            BadRequestException::class,
            '{"error":{"code":"BAD_REQUEST","message":"Invalid request payload","data":[{"name":"posting_number","code":"EMPTY","value":"","message":""}]}}',
            [
                ['name' => 'posting_number', 'code' => 'EMPTY', 'value' => '', 'message' => ''],
            ],
        ];
    }
}
