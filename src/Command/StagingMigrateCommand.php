<?php

namespace App\Command;

use App\DataWarehouseStageMigrator\ArtistMigrator;
use App\DataWarehouseStageMigrator\CustomerMigrator;
use App\DataWarehouseStageMigrator\MusicLabelMigrator;
use App\DataWarehouseStageMigrator\ReleaseMigrator;
use App\DataWarehouseStageMigrator\StageEntityPersister;
use App\DataWarehouseStageMigrator\StreamingServiceMigrator;
use Doctrine\ORM\EntityManagerInterface;
use Swoole\Coroutine\Channel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StagingMigrateCommand extends Command
{
    protected static $defaultName = 'app:staging:migrate';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EntityManagerInterface
     */
    private $stagingEntityManager;
    /**
     * @var ArtistMigrator
     */
    private $artistMigrator;
    /**
     * @var StageEntityPersister
     */
    private $entityPersister;
    /**
     * @var StreamingServiceMigrator
     */
    private $streamingServiceMigrator;
    /**
     * @var MusicLabelMigrator
     */
    private $musicLabelMigrator;
    /**
     * @var CustomerMigrator
     */
    private $customerMigrator;
    /**
     * @var ReleaseMigrator
     */
    private $releaseMigrator;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityManagerInterface $stagingEntityManager,
        ArtistMigrator $artistMigrator,
        StreamingServiceMigrator $streamingServiceMigrator,
        MusicLabelMigrator $musicLabelMigrator,
        CustomerMigrator $customerMigrator,
        ReleaseMigrator $releaseMigrator,
        StageEntityPersister $entityPersister
    )
    {
        $this->entityManager = $entityManager;
        $this->stagingEntityManager = $stagingEntityManager;
        $this->artistMigrator = $artistMigrator;
        $this->entityPersister = $entityPersister;
        $this->streamingServiceMigrator = $streamingServiceMigrator;
        $this->musicLabelMigrator = $musicLabelMigrator;
        $this->customerMigrator = $customerMigrator;
        $this->releaseMigrator = $releaseMigrator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command');
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

//        Runtime::enableCoroutine();
        $entityChannel = new Channel(5000);
        $progressBarChannel = new Channel(1);
        $schedulerChannel = new Channel(1);

        if (!$output instanceof ConsoleOutput) {
            $io->error('Should never happen.');
            exit(1);
        }

        ProgressBar::setFormatDefinition('minimal', '%message% %current%/%max% [%percent%%] %elapsed:6s%/%estimated:-6s% %memory:6s%');

        $progressBars = $this->makeProgressBars([
            'Migrating artists            ..',
            'Migrating streaming services ..',
            'Migrating music labels       ..',
            'Migrating customers          ..',
            'Migrating releases           ..',
        ], $output);

        go(function () use ($entityChannel, $io) {
            $this->entityPersister->run($entityChannel, $io);
        });

        go(function () use ($progressBars, $io, $entityChannel, $progressBarChannel, $schedulerChannel) {
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

            $entityChannel->close();
            $schedulerChannel->close();
        });

        $migrators = [
            'artists' => [
                'deps' => [],
                'callable' => function () use ($entityChannel, $progressBarChannel, $schedulerChannel) {
                    $this->artistMigrator->migrate($entityChannel, $progressBarChannel, 1);
                    $schedulerChannel->push('artists');
                    unset($this->artistMigrator);
                },
            ],
            'streaming_services' => [
                'deps' => [],
                'callable' => function () use ($entityChannel, $progressBarChannel, $schedulerChannel) {
                    $this->streamingServiceMigrator->migrate($entityChannel, $progressBarChannel, 2);
                    $schedulerChannel->push('streaming_services');
                    unset($this->streamingServiceMigrator);
                },
            ],
            'music_labels' => [
                'deps' => [],
                'callable' => function () use ($entityChannel, $progressBarChannel, $schedulerChannel) {
                    $this->musicLabelMigrator->migrate($entityChannel, $progressBarChannel, 3);
                    $schedulerChannel->push('music_labels');
                    unset($this->musicLabelMigrator);
                },
            ],
            'customers' => [
                'deps' => [],
                'callable' => function () use ($entityChannel, $progressBarChannel, $schedulerChannel) {
                    $this->customerMigrator->migrate($entityChannel, $progressBarChannel, 4);
                    $schedulerChannel->push('customers');
                    unset($this->customerMigrator);
                },
            ],
            'releases' => [
                'deps' => ['artists'],
                'callable' => function () use ($entityChannel, $progressBarChannel, $schedulerChannel) {
                    $this->releaseMigrator->migrate($entityChannel, $progressBarChannel, 5);
                    $schedulerChannel->push('releases');
                    unset($this->releaseMigrator);
                },
            ],
        ];

        go(function () use ($schedulerChannel, $migrators) {
            $ran = [];
            $finished = [];

            do {
                foreach ($migrators as $name => ['callable' => $migrator, 'deps' => $deps]) {
                    if (isset($ran[$name])) {
                        continue;
                    }

                    foreach ($deps as $dep) {
                        if (!isset($finished[$dep])) {
                            continue 2;
                        }
                    }

                    $ran[$name] = true;
                    go($migrator);
                }

                $data = $schedulerChannel->pop();
                if (\is_string($data)) {
                    $finished[$data] = true;
                }

            } while (\count($ran) !== \count($migrators));
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
