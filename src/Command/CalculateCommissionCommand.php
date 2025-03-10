<?php

namespace App\Command;

use App\Service\CommissionCalculator;
use App\Service\CsvParser;
use Symfony\Component\Console\{Attribute\AsCommand,
    Command\Command,
    Input\InputArgument,
    Input\InputInterface,
    Output\OutputInterface};
use Exception;

#[AsCommand(
    name: 'commission-counter:calculate',
    description: 'Calculate commission based on CSV input'
)]
class CalculateCommissionCommand extends Command
{
    private CsvParser $csvParser;
    private CommissionCalculator $commissionCalculator;

    public function __construct(CsvParser $csvParser, CommissionCalculator $commissionCalculator)
    {
        $this->csvParser = $csvParser;
        $this->commissionCalculator = $commissionCalculator;
        parent::__construct();

    }

    protected function configure(): void
    {
        $this->addArgument('inputFile', InputArgument::REQUIRED, 'Path to input file');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $inputFile = $input->getArgument('inputFile');
        $this->csvParser->setFilePath($inputFile);

        $parser = $this->csvParser->getIterator();

        foreach ($parser as $operation) {
            $fee = $this->commissionCalculator->calculate($operation);
            $output->writeln(number_format($fee, 2));
        }

        return Command::SUCCESS;
    }
}
