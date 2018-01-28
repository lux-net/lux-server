<?php 
namespace Neos\Flow\Command;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Util\Debug;
use Doctrine\DBAL\Migrations\MigrationException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Error\Debugger;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\Package;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\Persistence\Doctrine\Service as DoctrineService;
use Neos\Utility\Files;

/**
 * Command controller for tasks related to Doctrine
 *
 * @Flow\Scope("singleton")
 */
class DoctrineCommandController_Original extends CommandController
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @Flow\Inject
     * @var DoctrineService
     */
    protected $doctrineService;

    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * Injects the Flow settings, only the persistence part is kept for further use
     *
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings['persistence'];
    }

    /**
     * Compile the Doctrine proxy classes
     *
     * @return void
     * @Flow\Internal
     */
    public function compileProxiesCommand()
    {
        $this->doctrineService->compileProxies();
    }

    /**
     * Validate the class/table mappings
     *
     * Checks if the current class model schema is valid. Any inconsistencies
     * in the relations between models (for example caused by wrong or
     * missing annotations) will be reported.
     *
     * Note that this does not check the table structure in the database in
     * any way.
     *
     * @return void
     * @see neos.flow:doctrine:entitystatus
     */
    public function validateCommand()
    {
        $this->outputLine();
        $classesAndErrors = $this->doctrineService->validateMapping();
        if (count($classesAndErrors) === 0) {
            $this->outputLine('Mapping validation passed, no errors were found.');
        } else {
            $this->outputLine('Mapping validation FAILED!');
            foreach ($classesAndErrors as $className => $errors) {
                $this->outputLine('  %s', [$className]);
                foreach ($errors as $errorMessage) {
                    $this->outputLine('    %s', [$errorMessage]);
                }
            }
            $this->quit(1);
        }
    }

    /**
     * Create the database schema
     *
     * Creates a new database schema based on the current mapping information.
     *
     * It expects the database to be empty, if tables that are to be created already
     * exist, this will lead to errors.
     *
     * @param string $output A file to write SQL to, instead of executing it
     * @return void
     * @see neos.flow:doctrine:update
     * @see neos.flow:doctrine:migrate
     */
    public function createCommand($output = null)
    {
        if (!$this->isDatabaseConfigured()) {
            $this->outputLine('Database schema creation has been SKIPPED, the driver and host backend options are not set in /Configuration/Settings.yaml.');
            $this->quit(1);
        }

        $this->doctrineService->createSchema($output);
        if ($output === null) {
            $this->outputLine('Created database schema.');
        } else {
            $this->outputLine('Wrote schema creation SQL to file "' . $output . '".');
        }
    }

    /**
     * Update the database schema
     *
     * Updates the database schema without using existing migrations.
     *
     * It will not drop foreign keys, sequences and tables, unless <u>--unsafe-mode</u> is set.
     *
     * @param boolean $unsafeMode If set, foreign keys, sequences and tables can potentially be dropped.
     * @param string $output A file to write SQL to, instead of executing the update directly
     * @return void
     * @see neos.flow:doctrine:create
     * @see neos.flow:doctrine:migrate
     */
    public function updateCommand($unsafeMode = false, $output = null)
    {
        if (!$this->isDatabaseConfigured()) {
            $this->outputLine('Database schema update has been SKIPPED, the driver and host backend options are not set in /Configuration/Settings.yaml.');
            $this->quit(1);
        }

        $this->doctrineService->updateSchema(!$unsafeMode, $output);
        if ($output === null) {
            $this->outputLine('Executed a database schema update.');
        } else {
            $this->outputLine('Wrote schema update SQL to file "' . $output . '".');
        }
    }

    /**
     * Show the current status of entities and mappings
     *
     * Shows basic information about which entities exist and possibly if their
     * mapping information contains errors or not.
     *
     * To run a full validation, use the validate command.
     *
     * @param boolean $dumpMappingData If set, the mapping data will be output
     * @param string $entityClassName If given, the mapping data for just this class will be output
     * @return void
     * @see neos.flow:doctrine:validate
     */
    public function entityStatusCommand($dumpMappingData = false, $entityClassName = null)
    {
        $info = $this->doctrineService->getEntityStatus();

        if ($info === []) {
            $this->output('You do not have any mapped Doctrine ORM entities according to the current configuration. ');
            $this->outputLine('If you have entities or mapping files you should check your mapping configuration for errors.');
        } else {
            $this->outputLine('Found %d mapped entities:', [count($info)]);
            $this->outputLine();
            if ($entityClassName === null) {
                foreach ($info as $entityClassName => $entityStatus) {
                    if ($entityStatus instanceof ClassMetadata) {
                        $this->outputLine('<success>[OK]</success>   %s', [$entityClassName]);
                        if ($dumpMappingData) {
                            Debugger::clearState();
                            $this->outputLine(Debugger::renderDump($entityStatus, 0, true, true));
                        }
                    } else {
                        $this->outputLine('<error>[FAIL]</error> %s', [$entityClassName]);
                        $this->outputLine($entityStatus);
                        $this->outputLine();
                    }
                }
            } else {
                if (array_key_exists($entityClassName, $info) && $info[$entityClassName] instanceof ClassMetadata) {
                    $entityStatus = $info[$entityClassName];
                    $this->outputLine('<success>[OK]</success>   %s', [$entityClassName]);
                    if ($dumpMappingData) {
                        Debugger::clearState();
                        $this->outputLine(Debugger::renderDump($entityStatus, 0, true, true));
                    }
                } else {
                    $this->outputLine('<info>[FAIL]</info> %s', [$entityClassName]);
                    $this->outputLine('Class not found.');
                    $this->outputLine();
                }
            }
        }
    }

    /**
     * Run arbitrary DQL and display results
     *
     * Any DQL queries passed after the parameters will be executed, the results will be output:
     *
     * doctrine:dql --limit 10 'SELECT a FROM Neos\Flow\Security\Account a'
     *
     * @param integer $depth How many levels deep the result should be dumped
     * @param string $hydrationMode One of: object, array, scalar, single-scalar, simpleobject
     * @param integer $offset Offset the result by this number
     * @param integer $limit Limit the result to this number
     * @return void
     * @throws \InvalidArgumentException
     */
    public function dqlCommand($depth = 3, $hydrationMode = 'array', $offset = null, $limit = null)
    {
        if (!$this->isDatabaseConfigured()) {
            $this->outputLine('DQL query is not possible, the driver and host backend options are not set in /Configuration/Settings.yaml.');
            $this->quit(1);
        }

        $dqlStatements = $this->request->getExceedingArguments();
        $hydrationModeConstant = 'Doctrine\ORM\Query::HYDRATE_' . strtoupper(str_replace('-', '_', $hydrationMode));
        if (!defined($hydrationModeConstant)) {
            throw new \InvalidArgumentException('Hydration mode "' . $hydrationMode . '" does not exist. It should be either: object, array, scalar or single-scalar.');
        }

        foreach ($dqlStatements as $dql) {
            $resultSet = $this->doctrineService->runDql($dql, constant($hydrationModeConstant), $offset, $limit);
            Debug::dump($resultSet, $depth);
        }
    }

    /**
     * Show the current migration status
     *
     * Displays the migration configuration as well as the number of
     * available, executed and pending migrations.
     *
     * @param boolean $showMigrations Output a list of all migrations and their status
     * @param boolean $showDescriptions Show descriptions for the migrations (enables versions display)
     * @return void
     * @see neos.flow:doctrine:migrate
     * @see neos.flow:doctrine:migrationexecute
     * @see neos.flow:doctrine:migrationgenerate
     * @see neos.flow:doctrine:migrationversion
     */
    public function migrationStatusCommand($showMigrations = false, $showDescriptions = false)
    {
        if (!$this->isDatabaseConfigured()) {
            $this->outputLine('Doctrine migration status not available, the driver and host backend options are not set in /Configuration/Settings.yaml.');
            $this->quit(1);
        }

        if ($showDescriptions) {
            $showMigrations = true;
        }

        $this->outputLine($this->doctrineService->getFormattedMigrationStatus($showMigrations, $showDescriptions));
    }

    /**
     * Migrate the database schema
     *
     * Adjusts the database structure by applying the pending
     * migrations provided by currently active packages.
     *
     * @param string $version The version to migrate to
     * @param string $output A file to write SQL to, instead of executing it
     * @param boolean $dryRun Whether to do a dry run or not
     * @param boolean $quiet If set, only the executed migration versions will be output, one per line
     * @return void
     * @see neos.flow:doctrine:migrationstatus
     * @see neos.flow:doctrine:migrationexecute
     * @see neos.flow:doctrine:migrationgenerate
     * @see neos.flow:doctrine:migrationversion
     */
    public function migrateCommand($version = null, $output = null, $dryRun = false, $quiet = false)
    {
        if (!$this->isDatabaseConfigured()) {
            $this->outputLine('Doctrine migration not possible, the driver and host backend options are not set in /Configuration/Settings.yaml.');
            $this->quit(1);
        }

        try {
            $result = $this->doctrineService->executeMigrations($version, $output, $dryRun, $quiet);
            if ($result == '') {
                if (!$quiet) {
                    $this->outputLine('No migration was necessary.');
                }
            } elseif ($output === null) {
                $this->outputLine($result);
            } else {
                if (!$quiet) {
                    $this->outputLine('Wrote migration SQL to file "' . $output . '".');
                }
            }

            $this->emitAfterDatabaseMigration();
        } catch (\Exception $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * @return void
     * @Flow\Signal
     */
    protected function emitAfterDatabaseMigration()
    {
    }

    /**
     * Execute a single migration
     *
     * Manually runs a single migration in the given direction.
     *
     * @param string $version The migration to execute
     * @param string $direction Whether to execute the migration up (default) or down
     * @param string $output A file to write SQL to, instead of executing it
     * @param boolean $dryRun Whether to do a dry run or not
     * @return void
     * @see neos.flow:doctrine:migrate
     * @see neos.flow:doctrine:migrationstatus
     * @see neos.flow:doctrine:migrationgenerate
     * @see neos.flow:doctrine:migrationversion
     */
    public function migrationExecuteCommand($version, $direction = 'up', $output = null, $dryRun = false)
    {
        if (!$this->isDatabaseConfigured()) {
            $this->outputLine('Doctrine migration not possible, the driver and host backend options are not set in /Configuration/Settings.yaml.');
            $this->quit(1);
        }

        try {
            $this->outputLine($this->doctrineService->executeMigration($version, $direction, $output, $dryRun));
        } catch (\Exception $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * Mark/unmark migrations as migrated
     *
     * If <u>all</u> is given as version, all available migrations are marked
     * as requested.
     *
     * @param string $version The migration to execute
     * @param boolean $add The migration to mark as migrated
     * @param boolean $delete The migration to mark as not migrated
     * @return void
     * @throws \InvalidArgumentException
     * @see neos.flow:doctrine:migrate
     * @see neos.flow:doctrine:migrationstatus
     * @see neos.flow:doctrine:migrationexecute
     * @see neos.flow:doctrine:migrationgenerate
     */
    public function migrationVersionCommand($version, $add = false, $delete = false)
    {
        if (!$this->isDatabaseConfigured()) {
            $this->outputLine('Doctrine migration not possible, the driver and host backend options are not set in /Configuration/Settings.yaml.');
            $this->quit(1);
        }

        if ($add === false && $delete === false) {
            throw new \InvalidArgumentException('You must specify whether you want to --add or --delete the specified version.');
        }
        try {
            $this->doctrineService->markAsMigrated($version, $add ?: false);
        } catch (MigrationException $exception) {
            $this->outputLine($exception->getMessage());
            $this->quit(1);
        }
    }

    /**
     * Generate a new migration
     *
     * If $diffAgainstCurrent is TRUE (the default), it generates a migration file
     * with the diff between current DB structure and the found mapping metadata.
     *
     * Otherwise an empty migration skeleton is generated.
     *
     * Only includes tables/sequences matching the $filterExpression regexp when
     * diffing models and existing schema. Include delimiters in the expression!
     * The use of
     *
     *  --filter-expression '/^acme_com/'
     *
     * would only create a migration touching tables starting with "acme_com".
     *
     * Note: A filter-expression will overrule any filter configured through the
     * Neos.Flow.persistence.doctrine.migrations.ignoredTables setting
     *
     * @param boolean $diffAgainstCurrent Whether to base the migration on the current schema structure
     * @param string $filterExpression Only include tables/sequences matching the filter expression regexp
     * @param boolean $force Generate migrations even if there are migrations left to execute
     * @return void
     * @see neos.flow:doctrine:migrate
     * @see neos.flow:doctrine:migrationstatus
     * @see neos.flow:doctrine:migrationexecute
     * @see neos.flow:doctrine:migrationversion
     */
    public function migrationGenerateCommand($diffAgainstCurrent = true, $filterExpression = null, $force = false)
    {
        // "driver" is used only for Doctrine, thus we (mis-)use it here
        // additionally, when no host is set, skip this step, assuming no DB is needed
        if (!$this->isDatabaseConfigured()) {
            $this->outputLine('Doctrine migration generation has been SKIPPED, the driver and host backend options are not set in /Configuration/Settings.yaml.');
            $this->quit(1);
        }

        $migrationStatus = $this->doctrineService->getMigrationStatus();
        if ($migrationStatus['New Migrations'] > 0 && $force === false) {
            $this->outputLine('There are new migrations available. To avoid duplication those should be executed via `doctrine:migrate` before creating additional migrations.');
            $this->quit(1);
        }

        // use default filter expression from settings
        if ($filterExpression === null) {
            $ignoredTables = array_keys(array_filter($this->settings['doctrine']['migrations']['ignoredTables']));
            if ($ignoredTables !== array()) {
                $filterExpression = sprintf('/^(?!%s$).*$/xs', implode('$|', $ignoredTables));
            }
        }

        list($status, $migrationClassPathAndFilename) = $this->doctrineService->generateMigration($diffAgainstCurrent, $filterExpression);

        $this->outputLine('<info>%s</info>', [$status]);
        $this->outputLine();
        if ($migrationClassPathAndFilename) {
            $choices = ['Don\'t Move'];
            $packages = [null];

            /** @var Package $package */
            foreach ($this->packageManager->getAvailablePackages() as $package) {
                $type = $package->getComposerManifest('type');
                if ($type === null || (strpos($type, 'typo3-') !== 0 && strpos($type, 'neos-') !== 0)) {
                    continue;
                }
                $choices[] = $package->getPackageKey();
                $packages[] = $package;
            }
            $selectedPackageIndex = (integer)$this->output->select('Do you want to move the migration to one of these packages?', $choices, 0);
            $this->outputLine();

            if ($selectedPackageIndex !== 0) {
                /** @var Package $selectedPackage */
                $selectedPackage = $packages[$selectedPackageIndex];
                $targetPathAndFilename = Files::concatenatePaths([$selectedPackage->getPackagePath(), 'Migrations', $this->doctrineService->getDatabasePlatformName(), basename($migrationClassPathAndFilename)]);
                Files::createDirectoryRecursively(dirname($targetPathAndFilename));
                rename($migrationClassPathAndFilename, $targetPathAndFilename);
                $this->outputLine('The migration was moved to: <comment>%s</comment>', [substr($targetPathAndFilename, strlen(FLOW_PATH_PACKAGES))]);
                $this->outputLine();
                $this->outputLine('Next Steps:');
            } else {
                $this->outputLine('Next Steps:');
                $this->outputLine(sprintf('- Move <comment>%s</comment> to YourPackage/<comment>Migrations/%s/</comment>', $migrationClassPathAndFilename, $this->doctrineService->getDatabasePlatformName()));
            }
            $this->outputLine('- Review and adjust the generated migration.');
            $this->outputLine('- (optional) execute the migration using <comment>%s doctrine:migrate</comment>', [$this->getFlowInvocationString()]);
        }
    }

    /**
     * Output an error message and log the exception.
     *
     * @param \Exception $exception
     * @return void
     */
    protected function handleException(\Exception $exception)
    {
        $this->outputLine('<error>%s</error>', [$exception->getMessage()]);
        $this->outputLine();
        $this->outputLine('The exception details have been logged to the Flow system log.');
        $this->systemLogger->logException($exception);
        $this->quit(1);
    }

    protected function isDatabaseConfigured()
    {
        // "driver" is used only for Doctrine, thus we (mis-)use it here
        if ($this->settings['backendOptions']['driver'] === null) {
            return false;
        }

        return true;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Command;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Command controller for tasks related to Doctrine
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class DoctrineCommandController extends DoctrineCommandController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Flow\Command\DoctrineCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Command\DoctrineCommandController', $this);
        parent::__construct();
        if ('Neos\Flow\Command\DoctrineCommandController' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            'emitAfterDatabaseMigration' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
                ),
            ),
        );
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Flow\Command\DoctrineCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Command\DoctrineCommandController', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __clone()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitAfterDatabaseMigration()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterDatabaseMigration'])) {
            $result = parent::emitAfterDatabaseMigration();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterDatabaseMigration'] = TRUE;
            try {
            
                $methodArguments = [];

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Command\DoctrineCommandController', 'emitAfterDatabaseMigration', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAfterDatabaseMigration']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAfterDatabaseMigration']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Command\DoctrineCommandController', 'emitAfterDatabaseMigration', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterDatabaseMigration']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterDatabaseMigration']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
);
        $propertyVarTags = array (
  'settings' => 'array',
  'doctrineService' => 'Neos\\Flow\\Persistence\\Doctrine\\Service',
  'packageManager' => 'Neos\\Flow\\Package\\PackageManagerInterface',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'request' => 'Neos\\Flow\\Cli\\Request',
  'response' => 'Neos\\Flow\\Cli\\Response',
  'arguments' => 'Neos\\Flow\\Mvc\\Controller\\Arguments',
  'commandMethodName' => 'string',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'commandManager' => 'Neos\\Flow\\Cli\\CommandManager',
  'output' => 'Neos\\Flow\\Cli\\ConsoleOutput',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow'));
        $this->injectCommandManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Cli\CommandManager'));
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\Doctrine\Service', 'Neos\Flow\Persistence\Doctrine\Service', 'doctrineService', '206ac8e7bb82647119ac0486538b59dc', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\Doctrine\Service'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Package\PackageManagerInterface', 'Neos\Flow\Package\PackageManager', 'packageManager', 'b44be8eaae4695ec4f42edfbf6f8880a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Package\PackageManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'commandManager',
  2 => 'objectManager',
  3 => 'doctrineService',
  4 => 'packageManager',
  5 => 'systemLogger',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Command/DoctrineCommandController.php
#