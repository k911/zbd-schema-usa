<?php

namespace App\Command;

use App\DataWarehouseStageMigrator\StageEntityPersister;
use App\DataWarehouseStageMigrator\TrackLikeMigrator;
use App\DataWarehouseStageMigrator\TrackStreamMigrator;
use App\DataWarehouseStageMigrator\TransactionMigrator;
use Doctrine\ORM\EntityManagerInterface;
use Swoole\Coroutine\Channel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StagingInsertTrackStreamsCommand extends Command
{
    protected static $defaultName = 'app:staging:insert:streams';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EntityManagerInterface
     */
    private $stagingEntityManager;

    /**
     * @var TrackStreamMigrator
     */
    private $trackStreamMigrator;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityManagerInterface $stagingEntityManager,
        TrackStreamMigrator $trackStreamMigrator
    )
    {
        $this->entityManager = $entityManager;
        $this->stagingEntityManager = $stagingEntityManager;
        $this->trackStreamMigrator = $trackStreamMigrator;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Add a short description for your command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        if ($this->entityManager->getConnection()->ping()) {
            $io->success('Connected to source database!');
        }
        if ($this->stagingEntityManager->getConnection()->ping()) {
            $io->success('Connected to warehouse staging database!');
        }

        unset($this->entityManager, $this->stagingEntityManager);

        $progressBarChannel = new Channel(1);

        if (!$output instanceof ConsoleOutput) {
            $io->error('Should never happen.');
            exit(1);
        }

        ProgressBar::setFormatDefinition('minimal', '%message% %current%/%max% [%percent%%] %elapsed:6s%/%estimated:-6s% %memory:6s%');

        $progressBars = $this->makeProgressBars([
            'Inserting track streams ..',
        ], $output);

        go(function () use ($progressBarChannel) {
            try {
                $this->trackStreamMigrator->migrate($progressBarChannel, 1);
            } catch (\Throwable $exception) {
                dump($exception);
                $progressBarChannel->close();
            }
        });

        go(function () use ($progressBars, $io, $progressBarChannel) {
            $progressBarsCount = \count($progressBars);

            while (false !== $data = $progressBarChannel->pop()) {
                /** @var ProgressBar $progressBar */
                $progressBar = $progressBars[$data[1] - 1];
                switch ($data[0]) {
                    case 'inc':
                        $progressBar->advance($data[2]);
                        break;
                    case 'set_max':
                        $progressBar->setMaxSteps($data[2]);
                        break;
                    case 'finish':
                        $progressBar->finish();
                        --$progressBarsCount;
                        unset($progressBar, $progressBars[$data[1] - 1]);
                        if ($progressBarsCount === 0) {
                            $progressBarChannel->close();
                        }
                        break;
                    default:
                        $io->error(\sprintf('No handler for %s', $data[0]));
                        exit(1);
                }
            }

            $io->newLine();
            $io->success('Processing finished.');
        });
    }

    private function makeProgressBars(array $messages, ConsoleOutput $consoleOutput): array
    {
        return \array_map(function (array $data) {
            $progressBar = new ProgressBar($data[0]);
            $progressBar->setFormat('minimal');
            $progressBar->setMessage($data[1]);
            $progressBar->setRedrawFrequency(100);
            $progressBar->start(10000);
            return $progressBar;
        }, \array_map(function (string $message) use ($consoleOutput) {
            $section = $consoleOutput->section();
            return [$section, $message];
        }, $messages));
    }
}
