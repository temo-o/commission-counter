<?php

namespace App\Command;

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
    public function __construct(CsvParser $csvParser)
    {
        $this->csvParser = $csvParser;
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

        foreach ($parser as $row) {
            $output->writeln(print_r($row, true));
        }

        return Command::SUCCESS;
    }
}
