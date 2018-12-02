<?php

namespace App\Command;

use App\DataWarehouseStageMigrator\ArtistMigrator;
use App\DataWarehouseStageMigrator\StageEntityPersister;
use Doctrine\ORM\EntityManagerInterface;
use Swoole\Coroutine\Channel;
use Swoole\Event;
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

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityManagerInterface $stagingEntityManager,
        ArtistMigrator $artistMigrator,
        StageEntityPersister $entityPersister
    )
    {
        $this->entityManager = $entityManager;
        $this->stagingEntityManager = $stagingEntityManager;
        $this->artistMigrator = $artistMigrator;

        parent::__construct();
        $this->entityPersister = $entityPersister;
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
            dd('wtf');
        }

        go(function () use ($entityChannel, $io) {
            $this->entityPersister->run($entityChannel, $io);
        });

        go(function () use ($output, $io, $entityChannel, $progressBarChannel) {

            $section1 = $output->section();
            $section2 = $output->section();

            ProgressBar::setFormatDefinition('minimal', '%message%: %percent%%');

            $progressBar1 = new ProgressBar($section1);
            $progressBar1->setMessage('Migrating artists..');
            $progressBar1->setFormat('minimal');
            $progressBar2 = new ProgressBar($section2);
            $progressBar2->setMessage('Migrating something..');
            $progressBar2->setFormat('minimal');

            $progressBarsCount = 2;
            $progressBars = [
                1 => $progressBar1,
                2 => $progressBar2,
            ];

            while (false !== $data = $progressBarChannel->pop()) {
                switch ($data[0]) {
                    case 'inc':
                        $progressBars[$data[1]]->advance($data[2]);
                        break;
                    case 'set_max':
                        $progressBars[$data[1]]->setMaxSteps($data[2]);
                        break;
                    case 'finish':
                        $progressBars[$data[1]]->finish();
                        --$progressBarsCount;
                        if($progressBarsCount === 0) {
                            $progressBarChannel->close();
                        }
                        break;
                    default:
                        dd(\sprintf('No handler for %s', $data[0]));
                }
            }

            $io->newLine();
            $io->success('Processing finished.');

            $entityChannel->close();
        });

        go(function () use ($entityChannel, $progressBarChannel) {
            $this->artistMigrator->migrate($entityChannel, $progressBarChannel);
        });
        go(function () use ($progressBarChannel) {
            $max = 1000;
            $progressBarChannel->push(['set_max', 2, $max]);
            while ($max > 0) {
                $progressBarChannel->push(['inc', 2, 1]);
                --$max;
            }
            $progressBarChannel->push(['finish', 2, 1]);
        });
    }
}
