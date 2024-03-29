<?php

namespace App\Command;

use App\DataWarehouseStageMigrator\ArtistMigrator;
use App\DataWarehouseStageMigrator\CustomerMigrator;
use App\DataWarehouseStageMigrator\MusicLabelMigrator;
use App\DataWarehouseStageMigrator\ReleaseMigrator;
use App\DataWarehouseStageMigrator\StageEntityPersister;
use App\DataWarehouseStageMigrator\StreamingServiceMigrator;
use App\DataWarehouseStageMigrator\TrackMigrator;
use Doctrine\ORM\EntityManagerInterface;
use Swoole\Coroutine\Channel;
use Swoole\Runtime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StagingSyncCommand extends Command
{
    protected static $defaultName = 'app:staging:sync';

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
    /**
     * @var TrackMigrator
     */
    private $trackMigrator;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityManagerInterface $stagingEntityManager,
        ArtistMigrator $artistMigrator,
        StreamingServiceMigrator $streamingServiceMigrator,
        MusicLabelMigrator $musicLabelMigrator,
        CustomerMigrator $customerMigrator,
        ReleaseMigrator $releaseMigrator,
        TrackMigrator $trackMigrator,
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
        $this->trackMigrator = $trackMigrator;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronizes data between source database and data warehouse staging database. Adds only new ones.');
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
            'Syncing artists            ..',
            'Syncing streaming services ..',
            'Syncing music labels       ..',
            'Syncing customers          ..',
            'Syncing releases           ..',
            'Syncing tracks             ..',
        ], $output);

        go(function () use ($entityChannel, $io) {
            $this->entityPersister->run($entityChannel, $io);
        });

        go(function () use ($progressBars, $io, $entityChannel, $progressBarChannel, $schedulerChannel) {
            $progressBarsCount = \count($progressBars);

//            $counter = 0;
            while (false !== $data = $progressBarChannel->pop()) {
                /** @var ProgressBar $progressBar */
                $progressBar = $progressBars[$data[1] - 1];
                switch ($data[0]) {
                    case 'inc':
//                        ++$counter;
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

//                if ($counter % 10 === 0) {
//                    $entityChannel->push('flush');
//                }
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
            'tracks' => [
                'deps' => ['artists', 'releases'],
                'callable' => function () use ($entityChannel, $progressBarChannel, $schedulerChannel) {
                    $this->trackMigrator->migrate($entityChannel, $progressBarChannel, 6);
                    $schedulerChannel->push('tracks');
                    unset($this->trackMigrator);
                },
            ],
        ];

        go(function () use ($schedulerChannel, $progressBarChannel, $migrators) {
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
                    go(function() use ($migrator, $progressBarChannel) {
                        try {
                            $migrator();
                        } catch (\Throwable $exception) {
                            dump($exception);
                            $progressBarChannel->close();
                        }
                    });
                }

                $data = $schedulerChannel->pop();
                if(false === $data) {
                    break;
                }

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
