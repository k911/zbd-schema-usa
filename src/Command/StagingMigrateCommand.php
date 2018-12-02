<?php

namespace App\Command;

use App\DataWarehouseStageMigrator\ArtistMigrator;
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

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityManagerInterface $stagingEntityManager,
        ArtistMigrator $artistMigrator,
        StreamingServiceMigrator $streamingServiceMigrator,
        StageEntityPersister $entityPersister
    )
    {
        $this->entityManager = $entityManager;
        $this->stagingEntityManager = $stagingEntityManager;
        $this->artistMigrator = $artistMigrator;
        $this->entityPersister = $entityPersister;
        $this->streamingServiceMigrator = $streamingServiceMigrator;

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
        $entityChannel = new Channel(1);
        $progressBarChannel = new Channel(1);

        if (!$output instanceof ConsoleOutput) {
            $io->error('Should never happen.');
            exit(1);
        }

        go(function () use ($entityChannel, $io) {
            $this->entityPersister->run($entityChannel, $io);
        });

        go(function () use ($output, $io, $entityChannel, $progressBarChannel) {
            ProgressBar::setFormatDefinition('minimal', '%message% %current%/%max% [%percent%%] %elapsed:6s%/%estimated:-6s% %memory:6s%');

            $section1 = $output->section();
            $section2 = $output->section();
            $section3 = $output->section();

            $progressBar1 = new ProgressBar($section1);
            $progressBar1->setMessage('Migrating artists..');
            $progressBar1->setFormat('minimal');

            $progressBar2 = new ProgressBar($section2);
            $progressBar2->setMessage('Migrating streaming services..');
            $progressBar2->setFormat('minimal');

            $progressBar3 = new ProgressBar($section3);
            $progressBar3->setMessage('Migrating something..');
            $progressBar3->setFormat('minimal');

            $progressBars = [
                $progressBar1,
                $progressBar2,
                $progressBar3,
            ];
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
        });

        go(function () use ($entityChannel, $progressBarChannel) {
            $this->artistMigrator->migrate($entityChannel, $progressBarChannel, 1);
        });
        go(function () use ($entityChannel, $progressBarChannel) {
            $this->streamingServiceMigrator->migrate($entityChannel, $progressBarChannel, 2);
        });
        go(function () use ($progressBarChannel) {
            $max = 1000;
            $progressBarNo = 3;
            $progressBarChannel->push(['set_max', $progressBarNo, $max]);
            while ($max > 0) {
                $progressBarChannel->push(['inc', $progressBarNo, 1]);
                --$max;
            }
            $progressBarChannel->push(['finish', $progressBarNo, 1]);
        });
    }
}
