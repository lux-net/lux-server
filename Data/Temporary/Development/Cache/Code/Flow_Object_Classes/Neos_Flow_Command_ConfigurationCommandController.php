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

use Symfony\Component\Yaml\Yaml;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Configuration\ConfigurationSchemaValidator;
use Neos\Flow\Configuration\Exception\SchemaValidationException;
use Neos\Error\Messages\Error;
use Neos\Error\Messages\Notice;
use Neos\Utility\Arrays;
use Neos\Utility\SchemaGenerator;

/**
 * Configuration command controller for the Neos.Flow package
 *
 * @Flow\Scope("singleton")
 */
class ConfigurationCommandController_Original extends CommandController
{
    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @Flow\Inject(lazy = FALSE)
     * @var ConfigurationSchemaValidator
     */
    protected $configurationSchemaValidator;

    /**
     * @Flow\Inject
     * @var SchemaGenerator
     */
    protected $schemaGenerator;

    /**
     * Show the active configuration settings
     *
     * The command shows the configuration of the current context as it is used by Flow itself.
     * You can specify the configuration type and path if you want to show parts of the configuration.
     *
     * Display all settings:
     * ./flow configuration:show
     *
     * Display Flow persistence settings:
     * ./flow configuration:show --path TYPO3.Flow.persistence
     *
     * Display Flow Object Cache configuration
     * ./flow configuration:show --type Caches --path Flow_Object_Classes
     *
     * @param string $type Configuration type to show, defaults to Settings
     * @param string $path path to subconfiguration separated by "." like "Neos.Flow"
     * @return void
     */
    public function showCommand($type = 'Settings', $path = null)
    {
        $availableConfigurationTypes = $this->configurationManager->getAvailableConfigurationTypes();
        if (in_array($type, $availableConfigurationTypes)) {
            $configuration = $this->configurationManager->getConfiguration($type);
            if ($path !== null) {
                $configuration = Arrays::getValueByPath($configuration, $path);
            }
            $typeAndPath = $type . ($path ? ': ' . $path : '');
            if ($configuration === null) {
                $this->outputLine('<b>Configuration "%s" was empty!</b>', [$typeAndPath]);
            } else {
                $yaml = Yaml::dump($configuration, 99);
                $this->outputLine('<b>Configuration "%s":</b>', [$typeAndPath]);
                $this->outputLine();
                $this->outputLine($yaml . chr(10));
            }
        } else {
            $this->outputLine('<b>Configuration type "%s" was not found!</b>', [$type]);
            $this->outputLine('<b>Available configuration types:</b>');
            foreach ($availableConfigurationTypes as $availableConfigurationType) {
                $this->outputLine('  ' . $availableConfigurationType);
            }
            $this->outputLine();
            $this->outputLine('Hint: <b>%s configuration:show --type <configurationType></b>', [$this->getFlowInvocationString()]);
            $this->outputLine('      shows the configuration of the specified type.');
        }
    }

    /**
     * List registered configuration types
     *
     * @return void
     */
    public function listTypesCommand()
    {
        $this->outputLine('The following configuration types are registered:');
        $this->outputLine();

        foreach ($this->configurationManager->getAvailableConfigurationTypes() as $type) {
            $this->outputFormatted('- %s', [$type]);
        }
    }

    /**
     * Validate the given configuration
     *
     * <b>Validate all configuration</b>
     * ./flow configuration:validate
     *
     * <b>Validate configuration at a certain subtype</b>
     * ./flow configuration:validate --type Settings --path Neos.Flow.persistence
     *
     * You can retrieve the available configuration types with:
     * ./flow configuration:listtypes
     *
     * @param string $type Configuration type to validate
     * @param string $path path to the subconfiguration separated by "." like "Neos.Flow"
     * @param boolean $verbose if TRUE, output more verbose information on the schema files which were used
     * @return void
     */
    public function validateCommand($type = null, $path = null, $verbose = false)
    {
        if ($type === null) {
            $this->outputLine('Validating <b>all</b> configuration');
        } else {
            $this->outputLine('Validating <b>' . $type . '</b> configuration' . ($path !== null ? ' on path <b>' . $path . '</b>' : ''));
        }
        $this->outputLine();

        $validatedSchemaFiles = [];
        try {
            $result = $this->configurationSchemaValidator->validate($type, $path, $validatedSchemaFiles);
        } catch (SchemaValidationException $exception) {
            $this->outputLine('<b>Exception:</b>');
            $this->outputFormatted($exception->getMessage(), [], 4);
            $this->quit(2);
            return;
        }

        if ($verbose) {
            $this->outputLine('<b>Loaded Schema Files:</b>');
            foreach ($validatedSchemaFiles as $validatedSchemaFile) {
                $this->outputLine('- ' . substr($validatedSchemaFile, strlen(FLOW_PATH_ROOT)));
            }
            $this->outputLine();
            if ($result->hasNotices()) {
                $notices = $result->getFlattenedNotices();
                $this->outputLine('<b>%d notices:</b>', [count($notices)]);
                /** @var Notice $notice */
                foreach ($notices as $path => $pathNotices) {
                    foreach ($pathNotices as $notice) {
                        $this->outputLine(' - %s -> %s', [$path, $notice->render()]);
                    }
                }
                $this->outputLine();
            }
        }

        if ($result->hasErrors()) {
            $errors = $result->getFlattenedErrors();
            $this->outputLine('<b>%d errors were found:</b>', [count($errors)]);
            /** @var Error $error */
            foreach ($errors as $path => $pathErrors) {
                foreach ($pathErrors as $error) {
                    $this->outputLine(' - %s -> %s', [$path, $error->render()]);
                }
            }
            $this->quit(1);
        } else {
            $this->outputLine('<b>All Valid!</b>');
        }
    }

    /**
     * Generate a schema for the given configuration or YAML file.
     *
     * ./flow configuration:generateschema --type Settings --path Neos.Flow.persistence
     *
     * The schema will be output to standard output.
     *
     * @param string $type Configuration type to create a schema for
     * @param string $path path to the subconfiguration separated by "." like "Neos.Flow"
     * @param string $yaml YAML file to create a schema for
     * @return void
     */
    public function generateSchemaCommand($type = null, $path = null, $yaml = null)
    {
        $data = null;
        if ($yaml !== null && is_file($yaml) && is_readable($yaml)) {
            $data = Yaml::parse($yaml);
        } elseif ($type !== null) {
            $data = $this->configurationManager->getConfiguration($type);
            if ($path !== null) {
                $data = Arrays::getValueByPath($data, $path);
            }
        }

        if (empty($data)) {
            $this->outputLine('Data was not found or is empty');
            $this->quit(1);
        }

        $this->outputLine(Yaml::dump($this->schemaGenerator->generate($data), 99));
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Command;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Configuration command controller for the Neos.Flow package
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class ConfigurationCommandController extends ConfigurationCommandController_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\Command\ConfigurationCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Command\ConfigurationCommandController', $this);
        parent::__construct();
        if ('Neos\Flow\Command\ConfigurationCommandController' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }
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
  'configurationManager' => 'Neos\\Flow\\Configuration\\ConfigurationManager',
  'configurationSchemaValidator' => 'Neos\\Flow\\Configuration\\ConfigurationSchemaValidator',
  'schemaGenerator' => 'Neos\\Utility\\SchemaGenerator',
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
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Command\ConfigurationCommandController') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Command\ConfigurationCommandController', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectCommandManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Cli\CommandManager'));
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Configuration\ConfigurationManager', 'Neos\Flow\Configuration\ConfigurationManager', 'configurationManager', 'f559bc775c41b957515dc1c69b91d8b1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\ConfigurationManager'); });
        $this->configurationSchemaValidator = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\ConfigurationSchemaValidator');
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Utility\SchemaGenerator', 'Neos\Utility\SchemaGenerator', 'schemaGenerator', '6f9135baf3eab8266bf2e435eeb3a80c', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Utility\SchemaGenerator'); });
        $this->Flow_Injected_Properties = array (
  0 => 'commandManager',
  1 => 'objectManager',
  2 => 'configurationManager',
  3 => 'configurationSchemaValidator',
  4 => 'schemaGenerator',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Command/ConfigurationCommandController.php
#