<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use App\Service\ExchangeRateService;

class CommissionCalculatorTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();

        $exchangeRateServiceMock = $this->createMock(ExchangeRateService::class);

        $exchangeRateServiceMock->method('getExchangeRate')->willReturnMap([
            ['USD', 1.1497],
            ['JPY', 129.53],
            ['EUR', 1.0]
        ]);

        self::getContainer()->set(ExchangeRateService::class, $exchangeRateServiceMock);

        $application = static::getContainer()->get('console.command_loader')->get('commission-counter:calculate');
        $this->commandTester = new CommandTester($application);
    }

    public function testCommissionCalculation(): void
    {
        $inputFile = __DIR__ . '/data/input.csv';
        $expectedOutput = file(__DIR__ . '/data/expected_output.txt', FILE_IGNORE_NEW_LINES);

        $this->commandTester->execute([
            'inputFile' => $inputFile
        ]);

        $output = array_map('trim', explode("\n", trim($this->commandTester->getDisplay())));

        $this->assertEquals($expectedOutput, $output);
    }
}
