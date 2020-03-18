<?php

namespace Xigen\PhpCheck\Console\Command;

use Magento\Setup\Controller\Environment;
use Magento\Setup\Controller\ReadinessCheckInstaller;
use Magento\Setup\Controller\ReadinessCheckUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Environment check Php console command
 */
class Php extends Command
{
    const TYPE_OPTION = 'type';

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Cron Script Readiness Check
     *
     * @var \Magento\Setup\Model\CronScriptReadinessCheck
     */
    protected $cronScriptReadinessCheck;

    /**
     * PHP Readiness Check
     *
     * @var \Magento\Setup\Model\PhpReadinessCheck
     */
    protected $phpReadinessCheck;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Setup\FilePermissions $permissions
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Setup\Model\CronScriptReadinessCheck $cronScriptReadinessCheck
     * @param \Magento\Setup\Model\PhpReadinessCheck $phpReadinessCheck
     */
    public function __construct(
        \Magento\Framework\Setup\FilePermissions $permissions,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Setup\Model\CronScriptReadinessCheck $cronScriptReadinessCheck,
        \Magento\Setup\Model\PhpReadinessCheck $phpReadinessCheck,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->permissions = $permissions;
        $this->filesystem = $filesystem;
        $this->cronScriptReadinessCheck = $cronScriptReadinessCheck;
        $this->phpReadinessCheck = $phpReadinessCheck;
        $this->logger = $logger;
        $this->state = $state;
        $this->dateTime = $dateTime;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $type = $this->input->getOption(self::TYPE_OPTION) ?: 'installer';

        $progress = new ProgressBar($this->output, 2);
        $progress->start();

        $this->output->writeln('');

        $this->output->writeln((string) __('[%1] Start', $this->dateTime->gmtDate()));
        $version = $this->phpVersionAction($type);
        if (isset($version['data']['required'])) {
            $this->output->writeln((string) __(
                '[%1] <info>PHP Version</info> Required : %2',
                $this->dateTime->gmtDate(),
                $version['data']['required']
            ));
        }

        if (isset($version['data']['current'])) {
            $this->output->writeln((string) __(
                '[%1] <info>PHP Version</info> Current : %2',
                $this->dateTime->gmtDate(),
                $version['data']['current']
            ));
        }

        $this->output->writeln('');

        $progress->advance();

        $extensions = $this->phpExtensionsAction($type);
        if (isset($extensions['data']['required'])) {
            foreach ($extensions['data']['required'] as $required) {
                $this->output->writeln((string) __(
                    '[%1] <info>PHP Extension</info> Required : %2',
                    $this->dateTime->gmtDate(),
                    $required
                ));
            }
        }

        if (isset($extensions['data']['missing'])) {
            if (empty($extensions['data']['missing'])) {
                $this->output->writeln((string) __(
                    '[%1] <info>PHP Extension</info> Missing : <info>%2</info>',
                    $this->dateTime->gmtDate(),
                    'None'
                ));
            }
            foreach ($extensions['data']['missing'] as $missing) {
                $this->output->writeln((string) __(
                    '[%1] <error>PHP Extension</error> Missing : <error>%2</error>',
                    $this->dateTime->gmtDate(),
                    $missing
                ));
            }
        }

        $progress->advance();

        $progress->finish();
        $this->output->writeln('');
        $this->output->writeln((string) __('[%1] Finish', $this->dateTime->gmtDate()));
    }

    /**
     * Verifies php version
     * @return \Zend\View\Model\JsonModel
     */
    public function phpVersionAction($type)
    {
        $data = [];
        if ($type == ReadinessCheckInstaller::INSTALLER) {
            $data = $this->phpReadinessCheck->checkPhpVersion();
        } elseif ($type == ReadinessCheckUpdater::UPDATER) {
            $data = $this->getPhpChecksInfo(ReadinessCheck::KEY_PHP_VERSION_VERIFIED);
        }
        return $data;
    }

    /**
     * Verifies php verifications
     * @return \Zend\View\Model\JsonModel
     */
    public function phpExtensionsAction($type)
    {
        $data = [];
        if ($type == ReadinessCheckInstaller::INSTALLER) {
            $data = $this->phpReadinessCheck->checkPhpExtensions();
        } elseif ($type == ReadinessCheckUpdater::UPDATER) {
            $data = $this->getPhpChecksInfo(ReadinessCheck::KEY_PHP_EXTENSIONS_VERIFIED);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("xigen:check:platform");
        $this->setDescription("Check platform requirements");
        $this->setDefinition([
            new InputOption(self::TYPE_OPTION, '-l', InputOption::VALUE_OPTIONAL, 'Type'),

        ]);
        parent::configure();
    }
}
