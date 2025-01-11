<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MoneyManagerCommand extends Command
{
    protected static $defaultName = 'money-manager';
    private $dataFile = 'transactions.json'; // Path to the data file

    protected function configure()
    {
        $this
            ->setDescription('Manage your money')
            ->setHelp('This command allows you to manage your income and expenses.')
            ->addArgument('action', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Action to perform (add-income, add-expense, view-history)')
            ->addArgument('amount', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Amount for transaction (required for add-income or add-expense)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');
        $amount = $input->getArgument('amount');

        // Read existing data from the JSON file
        $data = $this->getData();

        switch ($action) {
            case 'add-income':
                if ($amount) {
                    $data['transactions'][] = [
                        'type' => 'income',
                        'amount' => $amount,
                        'balance' => $data['balance'] + $amount,
                        'date' => date('Y-m-d H:i:s')
                    ];
                    $data['balance'] += $amount;
                    $this->saveData($data);
                    $output->writeln("Added income: $amount");
                } else {
                    $output->writeln('Error: You must specify an amount for income.');
                }
                break;

            case 'add-expense':
                if ($amount) {
                    $data['transactions'][] = [
                        'type' => 'expense',
                        'amount' => $amount,
                        'balance' => $data['balance'] - $amount,
                        'date' => date('Y-m-d H:i:s')
                    ];
                    $data['balance'] -= $amount;
                    $this->saveData($data);
                    $output->writeln("Added expense: $amount");
                } else {
                    $output->writeln('Error: You must specify an amount for expense.');
                }
                break;

            case 'view-history':
                $output->writeln("Transaction History:");
                if (empty($data['transactions'])) {
                    $output->writeln("No transactions yet.");
                } else {
                    foreach ($data['transactions'] as $transaction) {
                        $output->writeln("[" . $transaction['date'] . "] " . ucfirst($transaction['type']) . ": " . $transaction['amount'] . " | Balance: " . $transaction['balance']);
                    }
                }
                break;

            default:
                $output->writeln('Invalid action. Use add-income, add-expense, or view-history.');
                break;
        }

        return Command::SUCCESS;
    }

    // Retrieve data from the JSON file
    private function getData(): array
    {
        if (!file_exists($this->dataFile)) {
            return [
                'balance' => 0,
                'transactions' => []
            ];
        }
        return json_decode(file_get_contents($this->dataFile), true);
    }

    // Save data back to the JSON file
    private function saveData(array $data)
    {
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}
