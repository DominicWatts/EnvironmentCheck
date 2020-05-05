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
     * @var \Magento\Framework\Setup\FilePermissions
     */
    protected $permissions;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Setup\Model\CronScriptReadinessCheck
     */
    protected $cronScriptReadinessCheck;

    /**
     * @var \Magento\Setup\Model\PhpReadinessCheck
     */
    protected $phpReadinessCheck;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
  
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;
  
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Console constructor
     * @param \Magento\Framework\Setup\FilePermissions $permissions
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Setup\Model\CronScriptReadinessCheck $cronScriptReadinessCheck
     * @param \Magento\Setup\Model\PhpReadinessCheck $phpReadinessCheck
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
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

        $this->output->writeln((string) __('[%1] Start', $this->dateTime->gmtDate()));

        $progress = new ProgressBar($this->output, 5);
        $progress->start();

        $this->output->writeln('');

        $memory = $this->phpMemoryLimitAction();
        if (isset($version['memory_limit']['error']) && $version['memory_limit']['error']) {
            $this->output->writeln((string) __(
                '[%1] <warning>PHP Memory</warning> : %2 : %3',
                $this->dateTime->gmtDate(),
                $version['memory_limit']['warning'],
                $version['memory_limit']['message']
            ));
        } else {
            $this->output->writeln((string) __(
                '[%1] <info>PHP Memory</info> : Requirements met',
                $this->dateTime->gmtDate()
            ));
        }

        $progress->advance();

        $this->output->writeln('');
       
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

        $progress->advance();

        $this->output->writeln('');

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
        $this->output->writeln('');

        $settings = $this->phpSettingsAction($type);
        foreach ($settings['data'] as $key => $setting) {
            $this->output->writeln((string) __(
                '[%1] <error>PHP Setting</error> Update : <error>%2</error>',
                $this->dateTime->gmtDate(),
                $setting['message']
            ));
        }

        $progress->advance();
        $this->output->writeln('');

        $permissions = $this->filePermissionsAction();
        if (isset($permissions['data']['missing'])) {
            if (empty($permissions['data']['missing'])) {
                $this->output->writeln((string) __(
                    '[%1] <info>Permissions</info> Missing : <info>%2</info>',
                    $this->dateTime->gmtDate(),
                    'None'
                ));
            }
            foreach ($permissions['data']['missing'] as $missing) {
                $this->output->writeln((string) __(
                    '[%1] <error>Permissions</error> Missing : <error>%2</error>',
                    $this->dateTime->gmtDate(),
                    $missing
                ));
            }
        }

        $progress->finish();
        $this->output->writeln('');
        $this->output->writeln((string) __('[%1] Finish', $this->dateTime->gmtDate()));
    }

    /**
     * Verifies php version
     * @return array
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
     * @return array
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
     * Checks PHP settings
     * @return array
     */
    public function phpSettingsAction($type)
    {
        $data = [];
        if ($type == ReadinessCheckInstaller::INSTALLER) {
            $data = $this->phpReadinessCheck->checkPhpSettings();
        } elseif ($type == ReadinessCheckUpdater::UPDATER) {
            $data = $this->getPhpChecksInfo(ReadinessCheck::KEY_PHP_SETTINGS_VERIFIED);
        }
        return $data;
    }

    /**
     * Checks PHP settings
     * @return array
     */
    public function phpMemoryLimitAction()
    {
        return $this->phpReadinessCheck->checkMemoryLimit();
    }

    /**
     * Verifies file permissions
     * @return array
     */
    public function filePermissionsAction()
    {
        $missingWritePermissionPaths = $this->permissions->getMissingWritablePathsForInstallation(true);
        $currentPaths = [];
        $requiredPaths = [];
        $missingPaths = [];
        if ($missingWritePermissionPaths) {
            foreach ($missingWritePermissionPaths as $key => $value) {
                $requiredPaths[] = $key;
                if (is_array($value)) {
                    $missingPaths[] = $key;
                } else {
                    $currentPaths[] = $key;
                }
            }
        }
        $data = [
            'data' => [
                'required' => $requiredPaths,
                'current' => $currentPaths,
                'missing' => $missingPaths,
            ],
        ];

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
            new InputOption(self::TYPE_OPTION, '-t', InputOption::VALUE_OPTIONAL, 'Type'),

        ]);
        parent::configure();
    }
}
