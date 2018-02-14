<?php

namespace Tests\Fei\Service\Logger\Client\Psr;

use Fei\Service\Logger\Client\Psr\ContextExtractor;
use Codeception\Test\Unit;
use Fei\Service\Logger\Entity\Notification;

/**
 * Class ContextExtractorTest
 *
 * @package Tests\Fei\Service\Logger\Client\Psr
 */
class ContextExtractorTest extends Unit
{
    /**
     * @dataProvider dataForTestExtractor
     */
    public function testExtractor($context, $expected)
    {
        $extractor = new ContextExtractor();

        $this->assertEquals($expected, $extractor->extract($context));
    }

    public function dataForTestExtractor()
    {
        return [
            0 => [
                [
                    'test1' => 1,
                    'test2' => 2,
                    'test3' => 3
                ],
                [
                    'context' => [
                        'test1' => 1,
                        'test2' => 2,
                        'test3' => 3
                    ]
                ]
            ],
            1 => [
                [
                    'flag' => 1,
                    'namespace' => 'test',
                    'user' => 'user',
                    'server' => 'test',
                    'command' => 'vim',
                    'origin' => 'test',
                    'category' => Notification::TECHNICAL,
                    'env' => 'test',
                    'test1' => 1,
                    'test2' => 2,
                    'test3' => 3
                ],
                [
                    'flag' => 1,
                    'namespace' => 'test',
                    'user' => 'user',
                    'server' => 'test',
                    'command' => 'vim',
                    'origin' => 'test',
                    'category' => Notification::TECHNICAL,
                    'env' => 'test',
                    'context' => [
                        'test1' => 1,
                        'test2' => 2,
                        'test3' => 3
                    ]
                ]
            ]
        ];
    }
}
