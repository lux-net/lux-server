<?php 
namespace Neos\Flow\Configuration;

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
use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Result;
use Neos\Utility\Arrays;
use Neos\Utility\Files;

/**
 * A validator for all configuration entries using Schema
 *
 * Writing Custom Schemata
 * =======================
 *
 * The schemas are searched in the path "Resources/Private/Schema" of all
 * active packages. The schema-filenames must match the pattern
 * [type].[path].schema.yaml. The type and/or the path can also be
 * expressed as subdirectories of Resources/Private/Schema. So
 * Settings/Neos/Flow.persistence.schema.yaml will match the same paths
 * like Settings.Neos.Flow.persistence.schema.yaml or
 * Settings/Neos.Flow/persistence.schema.yaml
 *
 * @Flow\Scope("singleton")
 */
class ConfigurationSchemaValidator_Original
{
    /**
     * @Flow\Inject
     * @var \Neos\Flow\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Package\PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var \Neos\Utility\SchemaValidator
     */
    protected $schemaValidator;

    /**
     * Validate the given $configurationType and $path
     *
     * @param string $configurationType (optional) the configuration type to validate. if NULL, validates all configuration.
     * @param string $path (optional) configuration path to validate
     * @param array $loadedSchemaFiles (optional). if given, will be filled with a list of loaded schema files
     * @return \Neos\Error\Messages\Result the result of the validation
     * @throws Exception\SchemaValidationException
     */
    public function validate($configurationType = null, $path = null, &$loadedSchemaFiles = [])
    {
        if ($configurationType === null) {
            $configurationTypes = $this->configurationManager->getAvailableConfigurationTypes();
        } else {
            $configurationTypes = [$configurationType];
        }

        $result = new Result();
        foreach ($configurationTypes as $configurationType) {
            $resultForEachType = $this->validateSingleType($configurationType, $path, $loadedSchemaFiles);
            $result->forProperty($configurationType)->merge($resultForEachType);
        }
        return $result;
    }

    /**
     * Validate a single configuration type
     *
     * @param string $configurationType the configuration typr to validate
     * @param string $path configuration path to validate, or NULL.
     * @param array $loadedSchemaFiles will be filled with a list of loaded schema files
     * @return \Neos\Error\Messages\Result
     * @throws Exception\SchemaValidationException
     */
    protected function validateSingleType($configurationType, $path, &$loadedSchemaFiles)
    {
        $availableConfigurationTypes = $this->configurationManager->getAvailableConfigurationTypes();
        if (in_array($configurationType, $availableConfigurationTypes) === false) {
            throw new Exception\SchemaValidationException('The configuration type "' . $configurationType . '" was not found. Only the following configuration types are supported: "' . implode('", "', $availableConfigurationTypes) . '"', 1364984886);
        }

        $configuration = $this->configurationManager->getConfiguration($configurationType);

        // find schema files for the given type and path
        $schemaFileInfos = [];
        $activePackages = $this->packageManager->getActivePackages();
        foreach ($activePackages as $package) {
            $packageKey = $package->getPackageKey();
            $packageSchemaPath = Files::concatenatePaths([$package->getResourcesPath(), 'Private/Schema']);
            if (is_dir($packageSchemaPath)) {
                foreach (Files::getRecursiveDirectoryGenerator($packageSchemaPath, '.schema.yaml') as $schemaFile) {
                    $schemaName = substr($schemaFile, strlen($packageSchemaPath) + 1, -strlen('.schema.yaml'));
                    $schemaNameParts = explode('.', str_replace('/', '.', $schemaName), 2);

                    $schemaType = $schemaNameParts[0];
                    $schemaPath = isset($schemaNameParts[1]) ? $schemaNameParts[1] : null;

                    if ($schemaType === $configurationType && ($path === null || strpos($schemaPath, $path) === 0)) {
                        $schemaFileInfos[] = [
                            'file' => $schemaFile,
                            'name' => $schemaName,
                            'path' => $schemaPath,
                            'packageKey' => $packageKey
                        ];
                    }
                }
            }
        }

        if (count($schemaFileInfos) === 0) {
            throw new Exception\SchemaValidationException('No schema files found for configuration type "' . $configurationType . '"' . ($path !== null ? ' and path "' . $path . '".': '.'), 1364985056);
        }

        $result = new Result();
        foreach ($schemaFileInfos as $schemaFileInfo) {
            $loadedSchemaFiles[] = $schemaFileInfo['file'];

            if ($schemaFileInfo['path'] !== null) {
                $data = Arrays::getValueByPath($configuration, $schemaFileInfo['path']);
            } else {
                $data = $configuration;
            }

            if (empty($data)) {
                $result->addNotice(new Notice('No configuration found, skipping schema "%s".', 1364985445, [substr($schemaFileInfo['file'], strlen(FLOW_PATH_ROOT))]));
            } else {
                $parsedSchema = Yaml::parse($schemaFileInfo['file']);
                $validationResultForSingleSchema = $this->schemaValidator->validate($data, $parsedSchema);

                if ($schemaFileInfo['path'] !== null) {
                    $result->forProperty($schemaFileInfo['path'])->merge($validationResultForSingleSchema);
                } else {
                    $result->merge($validationResultForSingleSchema);
                }
            }
        }

        return $result;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Configuration;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A validator for all configuration entries using Schema
 * 
 * Writing Custom Schemata
 * =======================
 * 
 * The schemas are searched in the path "Resources/Private/Schema" of all
 * active packages. The schema-filenames must match the pattern
 * [type].[path].schema.yaml. The type and/or the path can also be
 * expressed as subdirectories of Resources/Private/Schema. So
 * Settings/Neos/Flow.persistence.schema.yaml will match the same paths
 * like Settings.Neos.Flow.persistence.schema.yaml or
 * Settings/Neos.Flow/persistence.schema.yaml
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class ConfigurationSchemaValidator extends ConfigurationSchemaValidator_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\Configuration\ConfigurationSchemaValidator') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Configuration\ConfigurationSchemaValidator', $this);
        if ('Neos\Flow\Configuration\ConfigurationSchemaValidator' === get_class($this)) {
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
  'configurationManager' => '\\Neos\\Flow\\Configuration\\ConfigurationManager',
  'packageManager' => '\\Neos\\Flow\\Package\\PackageManagerInterface',
  'schemaValidator' => '\\Neos\\Utility\\SchemaValidator',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Configuration\ConfigurationSchemaValidator') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Configuration\ConfigurationSchemaValidator', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Configuration\ConfigurationManager', 'Neos\Flow\Configuration\ConfigurationManager', 'configurationManager', 'f559bc775c41b957515dc1c69b91d8b1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\ConfigurationManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Package\PackageManagerInterface', 'Neos\Flow\Package\PackageManager', 'packageManager', 'b44be8eaae4695ec4f42edfbf6f8880a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Package\PackageManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Utility\SchemaValidator', 'Neos\Utility\SchemaValidator', 'schemaValidator', 'e943b89441b0184bb40516c5ca642ac4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Utility\SchemaValidator'); });
        $this->Flow_Injected_Properties = array (
  0 => 'configurationManager',
  1 => 'packageManager',
  2 => 'schemaValidator',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Configuration/ConfigurationSchemaValidator.php
#