<?php return array (
  'Caches' => 
  array (
    'Eel_Expression_Code' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\PhpFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Fluid_TemplateCache' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\PhpFrontend',
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Default' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\VariableFrontend',
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
      'backendOptions' => 
      array (
        'defaultLifetime' => 0,
      ),
      'persistent' => false,
    ),
    'Flow_Cache_ResourceFiles' => 
    array (
    ),
    'Flow_Core' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_I18n_AvailableLocalesCache' => 
    array (
    ),
    'Flow_I18n_XmlModelCache' => 
    array (
    ),
    'Flow_I18n_Cldr_CldrModelCache' => 
    array (
    ),
    'Flow_I18n_Cldr_Reader_DatesReaderCache' => 
    array (
    ),
    'Flow_I18n_Cldr_Reader_NumbersReaderCache' => 
    array (
    ),
    'Flow_I18n_Cldr_Reader_PluralsReaderCache' => 
    array (
    ),
    'Flow_Monitor' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Mvc_Routing_Route' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Flow_Mvc_Routing_Resolve' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\VariableFrontend',
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Flow_Mvc_ViewConfigurations' => 
    array (
    ),
    'Flow_Object_Classes' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\PhpFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Object_Configuration' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Persistence_Doctrine' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Persistence_Doctrine_Results' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
      'backendOptions' => 
      array (
        'defaultLifetime' => 60,
      ),
    ),
    'Flow_Persistence_Doctrine_SecondLevel' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Reflection_Status' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Reflection_CompiletimeData' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_Reflection_RuntimeData' => 
    array (
    ),
    'Flow_Reflection_RuntimeClassSchemata' => 
    array (
    ),
    'Flow_Resource_Status' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
    ),
    'Flow_Security_Authorization_Privilege_Method' => 
    array (
    ),
    'Flow_Security_Cryptography_RSAWallet' => 
    array (
      'backendOptions' => 
      array (
        'defaultLifetime' => 30,
      ),
    ),
    'Flow_Security_Cryptography_HashService' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\StringFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
      'persistent' => true,
    ),
    'Flow_Session_MetaData' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Flow_Session_Storage' => 
    array (
      'backend' => 'Neos\\Cache\\Backend\\FileBackend',
    ),
    'Flow_Aop_RuntimeExpressions' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\PhpFrontend',
      'backend' => 'Neos\\Cache\\Backend\\SimpleFileBackend',
    ),
    'Flow_PropertyMapper' => 
    array (
      'frontend' => 'Neos\\Cache\\Frontend\\VariableFrontend',
      'backend' => 'Neos\\Cache\\Backend\\NullBackend',
    ),
  ),
  'Objects' => 
  array (
    'neos.errormessages' => 
    array (
    ),
    'Neos.Utility.Files' => 
    array (
    ),
    'Neos.Utility.Pdo' => 
    array (
      'Neos\\Utility\\PdoHelper' => 
      array (
        'autowiring' => 'off',
        'scope' => 'prototype',
      ),
    ),
    'Neos.Utility.OpcodeCache' => 
    array (
    ),
    'Neos.Cache' => 
    array (
    ),
    'neos.utilityunicode' => 
    array (
    ),
    'Neos.Utility.ObjectHandling' => 
    array (
    ),
    'Neos.Flow.Log' => 
    array (
    ),
    'Neos.Utility.Arrays' => 
    array (
    ),
    'Neos.Utility.MediaTypes' => 
    array (
    ),
    'Neos.Utility.Schema' => 
    array (
      'Neos\\Utility\\SchemaGenerator' => 
      array (
        'scope' => 'singleton',
      ),
      'Neos\\Utility\\SchemaValidator' => 
      array (
        'scope' => 'singleton',
      ),
    ),
    'typo3fluid.fluid' => 
    array (
    ),
    'psr.httpmessage' => 
    array (
    ),
    'paragonie.randomcompat' => 
    array (
    ),
    'ramsey.uuid' => 
    array (
    ),
    'Doctrine.Common.Collections' => 
    array (
    ),
    'doctrine.inflector' => 
    array (
    ),
    'doctrine.cache' => 
    array (
    ),
    'Doctrine.Common.Lexer' => 
    array (
    ),
    'doctrine.annotations' => 
    array (
    ),
    'doctrine.common' => 
    array (
    ),
    'Doctrine.DBAL' => 
    array (
    ),
    'doctrine.instantiator' => 
    array (
    ),
    'symfony.polyfillmbstring' => 
    array (
    ),
    'psr.log' => 
    array (
    ),
    'symfony.debug' => 
    array (
    ),
    'symfony.console' => 
    array (
    ),
    'Doctrine.ORM' => 
    array (
    ),
    'symfony.yaml' => 
    array (
    ),
    'zendframework.zendeventmanager' => 
    array (
    ),
    'zendframework.zendcode' => 
    array (
    ),
    'ocramius.packageversions' => 
    array (
    ),
    'ocramius.proxymanager' => 
    array (
    ),
    'doctrine.migrations' => 
    array (
    ),
    'symfony.domcrawler' => 
    array (
    ),
    'neos.composerplugin' => 
    array (
    ),
    'org.bovigo.vfs' => 
    array (
    ),
    'phpunit.phptokenstream' => 
    array (
    ),
    'Neos.Eel' => 
    array (
      'Neos\\Eel\\CompilingEvaluator' => 
      array (
        'properties' => 
        array (
          'expressionCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Eel_Expression_Code',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Eel\\EelEvaluatorInterface' => 
      array (
        'className' => 'Neos\\Eel\\CompilingEvaluator',
      ),
      'Neos\\Eel\\FlowQuery\\OperationResolverInterface' => 
      array (
        'className' => 'Neos\\Eel\\FlowQuery\\OperationResolver',
      ),
    ),
    'neos.utilitylock' => 
    array (
    ),
    'Neos.FluidAdaptor' => 
    array (
      'Neos\\FluidAdaptor\\Core\\Cache\\CacheAdaptor' => 
      array (
        'properties' => 
        array (
          'flowCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Fluid_TemplateCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\TagBuilder' => 
      array (
        'scope' => 'prototype',
      ),
    ),
    'Neos.Flow' => 
    array (
      'DateTime' => 
      array (
        'scope' => 'prototype',
        'autowiring' => 'off',
      ),
      'Composer\\Autoload\\ClassLoader' => 
      array (
        'scope' => 'singleton',
        'autowiring' => 'off',
      ),
      'Neos\\Cache\\CacheFactoryInterface' => 
      array (
        'className' => 'Neos\\Flow\\Cache\\CacheFactory',
      ),
      'Neos\\Flow\\Cache\\CacheFactory' => 
      array (
        'arguments' => 
        array (
          1 => 
          array (
            'setting' => 'Neos.Flow.context',
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Service' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_AvailableLocalesCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Cldr\\CldrModel' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_Cldr_CldrModelCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Xliff\\Service\\XliffFileProvider' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_XmlModelCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Xliff\\Service\\XliffReader' => 
      array (
        'properties' => 
        array (
          'i18nLogger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\LoggerFactory',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n',
                ),
                2 => 
                array (
                  'value' => 'Neos\\Flow\\Log\\Logger',
                ),
                3 => 
                array (
                  'setting' => 'Neos.Flow.log.i18nLogger.backend',
                ),
                4 => 
                array (
                  'setting' => 'Neos.Flow.log.i18nLogger.backendOptions',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Xliff\\Model\\FileAdapter' => 
      array (
        'properties' => 
        array (
          'i18nLogger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\LoggerFactory',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n',
                ),
                2 => 
                array (
                  'value' => 'Neos\\Flow\\Log\\Logger',
                ),
                3 => 
                array (
                  'setting' => 'Neos.Flow.log.i18nLogger.backend',
                ),
                4 => 
                array (
                  'setting' => 'Neos.Flow.log.i18nLogger.backendOptions',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Cldr\\Reader\\DatesReader' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_Cldr_Reader_DatesReaderCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Cldr\\Reader\\NumbersReader' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_Cldr_Reader_NumbersReaderCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\I18n\\Cldr\\Reader\\PluralsReader' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_I18n_Cldr_Reader_PluralsReaderCache',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Log\\Backend\\FileBackend' => 
      array (
        'autowiring' => 'off',
      ),
      'Neos\\Flow\\Log\\Backend\\NullBackend' => 
      array (
        'autowiring' => 'off',
      ),
      'Neos\\Flow\\Log\\SystemLoggerInterface' => 
      array (
        'scope' => 'singleton',
        'factoryObjectName' => 'Neos\\Flow\\Log\\LoggerFactory',
        'arguments' => 
        array (
          1 => 
          array (
            'value' => 'SystemLogger',
          ),
          2 => 
          array (
            'setting' => 'Neos.Flow.log.systemLogger.logger',
          ),
          3 => 
          array (
            'setting' => 'Neos.Flow.log.systemLogger.backend',
          ),
          4 => 
          array (
            'setting' => 'Neos.Flow.log.systemLogger.backendOptions',
          ),
        ),
      ),
      'Neos\\Flow\\Log\\SecurityLoggerInterface' => 
      array (
        'scope' => 'singleton',
        'factoryObjectName' => 'Neos\\Flow\\Log\\LoggerFactory',
        'arguments' => 
        array (
          1 => 
          array (
            'value' => 'Flow_Security',
          ),
          2 => 
          array (
            'value' => 'Neos\\Flow\\Log\\Logger',
          ),
          3 => 
          array (
            'setting' => 'Neos.Flow.log.securityLogger.backend',
          ),
          4 => 
          array (
            'setting' => 'Neos.Flow.log.securityLogger.backendOptions',
          ),
        ),
      ),
      'Neos\\Flow\\Log\\ThrowableStorageInterface' => 
      array (
        'scope' => 'singleton',
        'className' => 'Neos\\Flow\\Log\\ThrowableStorage\\FileStorage',
      ),
      'Neos\\Flow\\Log\\ThrowableStorage\\FileStorage' => 
      array (
        'properties' => 
        array (
          'storagePath' => 
          array (
            'setting' => 'Neos.Flow.log.throwables.fileStorage.path',
          ),
        ),
      ),
      'Neos\\Flow\\Monitor\\ChangeDetectionStrategy\\ModificationTimeStrategy' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Monitor',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Monitor\\FileMonitor' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Monitor',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Http\\Component\\ComponentChain' => 
      array (
        'factoryObjectName' => 'Neos\\Flow\\Http\\Component\\ComponentChainFactory',
        'arguments' => 
        array (
          1 => 
          array (
            'setting' => 'Neos.Flow.http.chain',
          ),
        ),
      ),
      'Neos\\Flow\\Mvc\\Routing\\RouterCachingService' => 
      array (
        'properties' => 
        array (
          'routeCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Mvc_Routing_Route',
                ),
              ),
            ),
          ),
          'resolveCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Mvc_Routing_Resolve',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Mvc\\ViewConfigurationManager' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Mvc_ViewConfigurations',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface' => 
      array (
        'className' => 'Neos\\Flow\\ObjectManagement\\ObjectManager',
        'scope' => 'singleton',
        'autowiring' => 'off',
      ),
      'Neos\\Flow\\ObjectManagement\\ObjectManager' => 
      array (
        'autowiring' => 'off',
      ),
      'Neos\\Flow\\ObjectManagement\\CompileTimeObjectManager' => 
      array (
        'autowiring' => 'off',
      ),
      'Neos\\Flow\\Package\\PackageManagerInterface' => 
      array (
        'scope' => 'singleton',
      ),
      'Doctrine\\Common\\Persistence\\ObjectManager' => 
      array (
        'scope' => 'singleton',
        'factoryObjectName' => 'Neos\\Flow\\Persistence\\Doctrine\\EntityManagerFactory',
      ),
      'Neos\\Flow\\Persistence\\PersistenceManagerInterface' => 
      array (
        'className' => 'Neos\\Flow\\Persistence\\Doctrine\\PersistenceManager',
      ),
      'Neos\\Flow\\Persistence\\Doctrine\\Logging\\SqlLogger' => 
      array (
        'properties' => 
        array (
          'logger' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Log\\LoggerFactory',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Sql_Queries',
                ),
                2 => 
                array (
                  'value' => 'Neos\\Flow\\Log\\Logger',
                ),
                3 => 
                array (
                  'value' => 'Neos\\Flow\\Log\\Backend\\FileBackend',
                ),
                4 => 
                array (
                  'setting' => 'Neos.Flow.log.sqlLogger.backendOptions',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Property\\PropertyMapper' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_PropertyMapper',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\ResourceManagement\\ResourceManager' => 
      array (
        'properties' => 
        array (
          'statusCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Resource_Status',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Authentication\\AuthenticationManagerInterface' => 
      array (
        'className' => 'Neos\\Flow\\Security\\Authentication\\AuthenticationProviderManager',
      ),
      'Neos\\Flow\\Security\\Cryptography\\RsaWalletServiceInterface' => 
      array (
        'className' => 'Neos\\Flow\\Security\\Cryptography\\RsaWalletServicePhp',
        'scope' => 'singleton',
        'properties' => 
        array (
          'keystoreCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Security_Cryptography_RSAWallet',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Authorization\\PrivilegeManagerInterface' => 
      array (
        'className' => 'Neos\\Flow\\Security\\Authorization\\PrivilegeManager',
      ),
      'Neos\\Flow\\Security\\Authorization\\FirewallInterface' => 
      array (
        'className' => 'Neos\\Flow\\Security\\Authorization\\FilterFirewall',
      ),
      'Neos\\Flow\\Security\\Cryptography\\HashService' => 
      array (
        'properties' => 
        array (
          'cache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Security_Cryptography_HashService',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Cryptography\\Pbkdf2HashingStrategy' => 
      array (
        'scope' => 'singleton',
        'arguments' => 
        array (
          1 => 
          array (
            'setting' => 'Neos.Flow.security.cryptography.Pbkdf2HashingStrategy.dynamicSaltLength',
          ),
          2 => 
          array (
            'setting' => 'Neos.Flow.security.cryptography.Pbkdf2HashingStrategy.iterationCount',
          ),
          3 => 
          array (
            'setting' => 'Neos.Flow.security.cryptography.Pbkdf2HashingStrategy.derivedKeyLength',
          ),
          4 => 
          array (
            'setting' => 'Neos.Flow.security.cryptography.Pbkdf2HashingStrategy.algorithm',
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Cryptography\\BCryptHashingStrategy' => 
      array (
        'scope' => 'singleton',
        'arguments' => 
        array (
          1 => 
          array (
            'setting' => 'Neos.Flow.security.cryptography.BCryptHashingStrategy.cost',
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Authorization\\Privilege\\Method\\MethodTargetExpressionParser' => 
      array (
        'scope' => 'singleton',
      ),
      'Neos\\Flow\\Security\\Authorization\\Privilege\\Method\\MethodPrivilegePointcutFilter' => 
      array (
        'scope' => 'singleton',
        'properties' => 
        array (
          'objectManager' => 
          array (
            'object' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
          ),
        ),
      ),
      'Neos\\Flow\\Security\\Authorization\\Privilege\\Entity\\Doctrine\\EntityPrivilegeExpressionEvaluator' => 
      array (
        'properties' => 
        array (
          'expressionCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Eel_Expression_Code',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Session\\SessionInterface' => 
      array (
        'scope' => 'singleton',
        'factoryObjectName' => 'Neos\\Flow\\Session\\SessionManagerInterface',
        'factoryMethodName' => 'getCurrentSession',
      ),
      'Neos\\Flow\\Session\\Session' => 
      array (
        'properties' => 
        array (
          'metaDataCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Session_MetaData',
                ),
              ),
            ),
          ),
          'storageCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Session_Storage',
                ),
              ),
            ),
          ),
        ),
      ),
      'Neos\\Flow\\Session\\SessionManagerInterface' => 
      array (
        'className' => 'Neos\\Flow\\Session\\SessionManager',
      ),
      'Neos\\Flow\\Session\\SessionManager' => 
      array (
        'properties' => 
        array (
          'metaDataCache' => 
          array (
            'object' => 
            array (
              'factoryObjectName' => 'Neos\\Flow\\Cache\\CacheManager',
              'factoryMethodName' => 'getCache',
              'arguments' => 
              array (
                1 => 
                array (
                  'value' => 'Flow_Session_MetaData',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'AgzHack.LightMarkerImporter' => 
    array (
    ),
    'AgzHack.Api' => 
    array (
    ),
    'AgzHack.Auth' => 
    array (
    ),
    'AgzHack.Geo' => 
    array (
    ),
    'Neos.Welcome' => 
    array (
    ),
    'Neos.Behat' => 
    array (
    ),
    'AgzHack.Lux' => 
    array (
    ),
    'AgzHack.KML' => 
    array (
    ),
    'phpunit.phptexttemplate' => 
    array (
    ),
    'phpunit.phpfileiterator' => 
    array (
    ),
    'phpunit.phptimer' => 
    array (
    ),
    'myclabs.deepcopy' => 
    array (
    ),
    'phpdocumentor.reflectioncommon' => 
    array (
    ),
    'phpdocumentor.typeresolver' => 
    array (
    ),
    'webmozart.assert' => 
    array (
    ),
    'phpdocumentor.reflectiondocblock' => 
    array (
    ),
    'sebastian.diff' => 
    array (
    ),
    'sebastian.recursioncontext' => 
    array (
    ),
    'sebastian.exporter' => 
    array (
    ),
    'sebastian.comparator' => 
    array (
    ),
    'phpspec.prophecy' => 
    array (
    ),
    'sebastian.codeunitreverselookup' => 
    array (
    ),
    'sebastian.environment' => 
    array (
    ),
    'sebastian.version' => 
    array (
    ),
    'theseer.tokenizer' => 
    array (
    ),
    'phpunit.phpcodecoverage' => 
    array (
    ),
    'phpunit.phpunitmockobjects' => 
    array (
    ),
    'sebastian.globalstate' => 
    array (
    ),
    'sebastian.objectreflector' => 
    array (
    ),
    'sebastian.objectenumerator' => 
    array (
    ),
    'sebastian.resourceoperations' => 
    array (
    ),
    'phpunit.phpunit' => 
    array (
    ),
    'beberlei.DoctrineExtensions' => 
    array (
    ),
    'nuovo.spreadsheetreader' => 
    array (
    ),
    'Neos.Kickstarter' => 
    array (
    ),
  ),
  'Settings' => 
  array (
    'Neos' => 
    array (
      'Flow' => 
      array (
        'aop' => 
        array (
          'globalObjects' => 
          array (
            'securityContext' => 'Neos\\Flow\\Security\\Context',
          ),
        ),
        'compatibility' => 
        array (
        ),
        'core' => 
        array (
          'context' => 'Development',
          'applicationPackageKey' => 'Neos.Flow',
          'applicationName' => 'Flow',
          'phpBinaryPathAndFilename' => (defined('PHP_BINDIR') ? constant('PHP_BINDIR') : null) . '/php',
          'subRequestEnvironmentVariables' => 
          array (
            'XDEBUG_CONFIG' => 'idekey=FLOW_SUBREQUEST remote_port=9001',
          ),
          'subRequestPhpIniPathAndFilename' => NULL,
          'subRequestIniEntries' => 
          array (
          ),
        ),
        'error' => 
        array (
          'exceptionHandler' => 
          array (
            'className' => 'Neos\\Flow\\Error\\DebugExceptionHandler',
            'defaultRenderingOptions' => 
            array (
              'viewClassName' => 'Neos\\FluidAdaptor\\View\\StandaloneView',
              'viewOptions' => 
              array (
              ),
              'renderTechnicalDetails' => true,
              'logException' => true,
            ),
            'renderingGroups' => 
            array (
              'notFoundExceptions' => 
              array (
                'matchingStatusCodes' => 
                array (
                  0 => 404,
                ),
                'options' => 
                array (
                  'logException' => false,
                  'templatePathAndFilename' => 'resource://Neos.Flow/Private/Templates/Error/Default.html',
                  'variables' => 
                  array (
                    'errorDescription' => 'Sorry, the page you requested was not found.',
                  ),
                ),
              ),
              'databaseConnectionExceptions' => 
              array (
                'matchingExceptionClassNames' => 
                array (
                  0 => 'Neos\\Flow\\Persistence\\Doctrine\\Exception\\DatabaseException',
                ),
                'options' => 
                array (
                  'templatePathAndFilename' => 'resource://Neos.Flow/Private/Templates/Error/Default.html',
                  'variables' => 
                  array (
                    'errorDescription' => 'Sorry, the database connection couldn\'t be established.',
                  ),
                ),
              ),
            ),
          ),
          'errorHandler' => 
          array (
            'exceptionalErrors' => 
            array (
              0 => (defined('E_USER_ERROR') ? constant('E_USER_ERROR') : null),
              1 => (defined('E_RECOVERABLE_ERROR') ? constant('E_RECOVERABLE_ERROR') : null),
              2 => (defined('E_WARNING') ? constant('E_WARNING') : null),
              3 => (defined('E_NOTICE') ? constant('E_NOTICE') : null),
              4 => (defined('E_USER_WARNING') ? constant('E_USER_WARNING') : null),
              5 => (defined('E_USER_NOTICE') ? constant('E_USER_NOTICE') : null),
              6 => (defined('E_STRICT') ? constant('E_STRICT') : null),
            ),
          ),
          'debugger' => 
          array (
            'ignoredClasses' => 
            array (
              'Neos\\\\Flow\\\\Aop.*' => true,
              'Neos\\\\Flow\\\\Cac.*' => true,
              'Neos\\\\Flow\\\\Core\\\\.*' => true,
              'Neos\\\\Flow\\\\Con.*' => true,
              'Neos\\\\Flow\\\\Http\\\\RequestHandler' => true,
              'Neos\\\\Flow\\\\Uti.*' => true,
              'Neos\\\\Flow\\\\Mvc\\\\Routing.*' => true,
              'Neos\\\\Flow\\\\Log.*' => true,
              'Neos\\\\Flow\\\\Obj.*' => true,
              'Neos\\\\Flow\\\\Pac.*' => true,
              'Neos\\\\Flow\\\\Persistence\\\\(?!Doctrine\\\\Mapping).*' => true,
              'Neos\\\\Flow\\\\Pro.*' => true,
              'Neos\\\\Flow\\\\Ref.*' => true,
              'Neos\\\\Flow\\\\Sec.*' => true,
              'Neos\\\\Flow\\\\Sig.*' => true,
              'Neos\\\\Flow\\\\.*ResourceManager' => true,
              'Neos\\\\FluidAdaptor\\\\.*' => true,
              '.+Service$' => true,
              '.+Repository$' => true,
              'PHPUnit_Framework_MockObject_InvocationMocker' => true,
            ),
          ),
        ),
        'mvc' => 
        array (
          'routes' => 
          array (
            'Neos.Welcome' => 
            array (
              'position' => 'start',
            ),
          ),
          'view' => 
          array (
            'defaultImplementation' => 'Neos\\FluidAdaptor\\View\\TemplateView',
          ),
        ),
        'http' => 
        array (
          'applicationToken' => 'MinorVersion',
          'baseUri' => NULL,
          'chain' => 
          array (
            'preprocess' => 
            array (
              'position' => 'before process',
              'chain' => 
              array (
                'trustedProxies' => 
                array (
                  'position' => 'start',
                  'component' => 'Neos\\Flow\\Http\\Component\\TrustedProxiesComponent',
                ),
              ),
            ),
            'process' => 
            array (
              'chain' => 
              array (
                'routing' => 
                array (
                  'position' => 'start',
                  'component' => 'Neos\\Flow\\Mvc\\Routing\\RoutingComponent',
                ),
                'dispatching' => 
                array (
                  'component' => 'Neos\\Flow\\Mvc\\DispatchComponent',
                ),
                'ajaxWidget' => 
                array (
                  'position' => 'before routing',
                  'component' => 'Neos\\FluidAdaptor\\Core\\Widget\\AjaxWidgetComponent',
                ),
              ),
            ),
            'postprocess' => 
            array (
              'chain' => 
              array (
                'standardsCompliance' => 
                array (
                  'position' => 'end',
                  'component' => 'Neos\\Flow\\Http\\Component\\StandardsComplianceComponent',
                ),
              ),
            ),
          ),
          'trustedProxies' => 
          array (
            'proxies' => '*',
            'headers' => 
            array (
              'clientIp' => 'Client-Ip,X-Forwarded-For,X-Forwarded,X-Cluster-Client-Ip,Forwarded-For,Forwarded',
              'host' => 'X-Forwarded-Host',
              'port' => 'X-Forwarded-Port',
              'proto' => 'X-Forwarded-Proto',
            ),
          ),
        ),
        'log' => 
        array (
          'systemLogger' => 
          array (
            'logger' => 'Neos\\Flow\\Log\\Logger',
            'backend' => 'Neos\\Flow\\Log\\Backend\\FileBackend',
            'backendOptions' => 
            array (
              'logFileURL' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Logs/System_Development.log',
              'createParentDirectories' => true,
              'severityThreshold' => (defined('LOG_DEBUG') ? constant('LOG_DEBUG') : null),
              'maximumLogFileSize' => 10485760,
              'logFilesToKeep' => 1,
              'logMessageOrigin' => false,
            ),
          ),
          'securityLogger' => 
          array (
            'backend' => 'Neos\\Flow\\Log\\Backend\\FileBackend',
            'backendOptions' => 
            array (
              'logFileURL' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Logs/Security_Development.log',
              'createParentDirectories' => true,
              'severityThreshold' => (defined('LOG_DEBUG') ? constant('LOG_DEBUG') : null),
              'maximumLogFileSize' => 10485760,
              'logFilesToKeep' => 1,
              'logIpAddress' => true,
            ),
          ),
          'sqlLogger' => 
          array (
            'backend' => 'Neos\\Flow\\Log\\Backend\\FileBackend',
            'backendOptions' => 
            array (
              'logFileURL' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Logs/Query_Development.log',
              'createParentDirectories' => true,
              'severityThreshold' => (defined('LOG_DEBUG') ? constant('LOG_DEBUG') : null),
              'maximumLogFileSize' => 10485760,
              'logFilesToKeep' => 1,
            ),
          ),
          'i18nLogger' => 
          array (
            'backend' => 'Neos\\Flow\\Log\\Backend\\FileBackend',
            'backendOptions' => 
            array (
              'logFileURL' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Logs/I18n_Development.log',
              'createParentDirectories' => true,
              'severityThreshold' => (defined('LOG_DEBUG') ? constant('LOG_DEBUG') : null),
              'maximumLogFileSize' => 1048576,
              'logFilesToKeep' => 1,
            ),
          ),
          'throwables' => 
          array (
            'fileStorage' => 
            array (
              'path' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Logs/Exceptions',
            ),
          ),
        ),
        'i18n' => 
        array (
          'defaultLocale' => 'en',
          'fallbackRule' => 
          array (
            'strict' => false,
            'order' => 
            array (
            ),
          ),
          'scan' => 
          array (
            'includePaths' => 
            array (
              '/Public/' => true,
              '/Private/' => true,
            ),
            'excludePatterns' => 
            array (
              '/node_modules/' => true,
              '/bower_components/' => true,
              '/\\..*/' => true,
            ),
          ),
        ),
        'object' => 
        array (
          'registerFunctionalTestClasses' => false,
          'includeClasses' => 
          array (
          ),
        ),
        'package' => 
        array (
          'inactiveByDefault' => 
          array (
            0 => 'neos.composerplugin',
            1 => 'Composer.Installers',
          ),
          'packagesPathByType' => 
          array (
            'typo3-flow-package' => 'Application',
            'neos-package' => 'Application',
            'typo3-flow-framework' => 'Framework',
            'neos-framework' => 'Framework',
          ),
        ),
        'persistence' => 
        array (
          'backendOptions' => 
          array (
            'driver' => 'pdo_mysql',
            'host' => 'lux_database',
            'dbname' => 'lux_db',
            'user' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'port' => '3306',
          ),
          'cacheAllQueryResults' => false,
          'doctrine' => 
          array (
            'enable' => true,
            'sqlLogger' => NULL,
            'filters' => 
            array (
              'Flow_Security_Entity_Filter' => 'Neos\\Flow\\Security\\Authorization\\Privilege\\Entity\\Doctrine\\SqlFilter',
            ),
            'dbal' => 
            array (
              'mappingTypes' => 
              array (
                'flow_json_array' => 
                array (
                  'dbType' => 'json_array',
                  'className' => 'Neos\\Flow\\Persistence\\Doctrine\\DataTypes\\JsonArrayType',
                ),
                'objectarray' => 
                array (
                  'dbType' => 'array',
                  'className' => 'Neos\\Flow\\Persistence\\Doctrine\\DataTypes\\ObjectArray',
                ),
              ),
            ),
            'eventSubscribers' => 
            array (
            ),
            'eventListeners' => 
            array (
            ),
            'secondLevelCache' => 
            array (
            ),
            'migrations' => 
            array (
              'ignoredTables' => 
              array (
              ),
            ),
          ),
        ),
        'reflection' => 
        array (
          'ignoredTags' => 
          array (
            'api' => true,
            'package' => true,
            'subpackage' => true,
            'license' => true,
            'copyright' => true,
            'author' => true,
            'const' => true,
            'see' => true,
            'todo' => true,
            'scope' => true,
            'fixme' => true,
            'test' => true,
            'expectedException' => true,
            'expectedExceptionMessage' => true,
            'expectedExceptionCode' => true,
            'depends' => true,
            'dataProvider' => true,
            'group' => true,
            'codeCoverageIgnore' => true,
            'requires' => true,
            'Given' => true,
            'When' => true,
            'Then' => true,
            'BeforeScenario' => true,
            'AfterScenario' => true,
            'fixtures' => true,
            'Isolated' => true,
            'AfterFeature' => true,
            'BeforeFeature' => true,
            'BeforeStep' => true,
            'AfterStep' => true,
            'WithoutSecurityChecks' => true,
            'covers' => true,
          ),
          'logIncorrectDocCommentHints' => false,
        ),
        'resource' => 
        array (
          'uploadExtensionBlacklist' => 
          array (
            'aspx' => true,
            'cgi' => true,
            'php3' => true,
            'php4' => true,
            'php5' => true,
            'phtml' => true,
            'php' => true,
            'pl' => true,
            'py' => true,
            'pyc' => true,
            'pyo' => true,
            'rb' => true,
          ),
          'storages' => 
          array (
            'defaultPersistentResourcesStorage' => 
            array (
              'storage' => 'Neos\\Flow\\ResourceManagement\\Storage\\WritableFileSystemStorage',
              'storageOptions' => 
              array (
                'path' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Persistent/Resources/',
              ),
            ),
            'defaultStaticResourcesStorage' => 
            array (
              'storage' => 'Neos\\Flow\\ResourceManagement\\Storage\\PackageStorage',
            ),
          ),
          'collections' => 
          array (
            'static' => 
            array (
              'storage' => 'defaultStaticResourcesStorage',
              'target' => 'localWebDirectoryStaticResourcesTarget',
              'pathPatterns' => 
              array (
                0 => '*/Resources/Public/',
                1 => '*/Resources/Public/*',
              ),
            ),
            'persistent' => 
            array (
              'storage' => 'defaultPersistentResourcesStorage',
              'target' => 'localWebDirectoryPersistentResourcesTarget',
            ),
          ),
          'targets' => 
          array (
            'localWebDirectoryStaticResourcesTarget' => 
            array (
              'target' => 'Neos\\Flow\\ResourceManagement\\Target\\FileSystemSymlinkTarget',
              'targetOptions' => 
              array (
                'path' => (defined('FLOW_PATH_WEB') ? constant('FLOW_PATH_WEB') : null) . '_Resources/Static/Packages/',
                'baseUri' => '_Resources/Static/Packages/',
                'extensionBlacklist' => 
                array (
                  'aspx' => true,
                  'cgi' => true,
                  'php3' => true,
                  'php4' => true,
                  'php5' => true,
                  'phtml' => true,
                  'php' => true,
                  'pl' => true,
                  'py' => true,
                  'pyc' => true,
                  'pyo' => true,
                  'rb' => true,
                ),
              ),
            ),
            'localWebDirectoryPersistentResourcesTarget' => 
            array (
              'target' => 'Neos\\Flow\\ResourceManagement\\Target\\FileSystemSymlinkTarget',
              'targetOptions' => 
              array (
                'path' => (defined('FLOW_PATH_WEB') ? constant('FLOW_PATH_WEB') : null) . '_Resources/Persistent/',
                'baseUri' => '_Resources/Persistent/',
                'extensionBlacklist' => 
                array (
                  'aspx' => true,
                  'cgi' => true,
                  'php3' => true,
                  'php4' => true,
                  'php5' => true,
                  'phtml' => true,
                  'php' => true,
                  'pl' => true,
                  'py' => true,
                  'pyc' => true,
                  'pyo' => true,
                  'rb' => true,
                ),
                'subdivideHashPathSegment' => false,
              ),
            ),
          ),
        ),
        'security' => 
        array (
          'firewall' => 
          array (
            'rejectAll' => false,
            'filters' => 
            array (
              'Neos.Flow:CsrfProtection' => 
              array (
                'pattern' => 'CsrfProtection',
                'interceptor' => 'CsrfTokenMissing',
              ),
            ),
          ),
          'authentication' => 
          array (
            'providers' => 
            array (
              'TokenProvider' => 
              array (
                'provider' => 'AgzHack\\Auth\\Provider\\StatelessTokenProvider',
                'token' => 'AgzHack\\Auth\\Token\\StatelessToken',
              ),
            ),
            'authenticationStrategy' => 'atLeastOneToken',
          ),
          'authorization' => 
          array (
            'allowAccessIfAllVotersAbstain' => false,
          ),
          'csrf' => 
          array (
            'csrfStrategy' => 'onePerSession',
          ),
          'cryptography' => 
          array (
            'hashingStrategies' => 
            array (
              'default' => 'bcrypt',
              'pbkdf2' => 'Neos\\Flow\\Security\\Cryptography\\Pbkdf2HashingStrategy',
              'bcrypt' => 'Neos\\Flow\\Security\\Cryptography\\BCryptHashingStrategy',
              'saltedmd5' => 'Neos\\Flow\\Security\\Cryptography\\SaltedMd5HashingStrategy',
            ),
            'Pbkdf2HashingStrategy' => 
            array (
              'dynamicSaltLength' => 8,
              'iterationCount' => 10000,
              'derivedKeyLength' => 64,
              'algorithm' => 'sha256',
            ),
            'BCryptHashingStrategy' => 
            array (
              'cost' => 14,
            ),
            'RSAWalletServicePHP' => 
            array (
              'keystorePath' => (defined('FLOW_PATH_DATA') ? constant('FLOW_PATH_DATA') : null) . 'Persistent/RsaWalletData',
              'openSSLConfiguration' => 
              array (
              ),
            ),
          ),
          'enable' => true,
        ),
        'session' => 
        array (
          'inactivityTimeout' => 3600,
          'name' => 'Neos_Flow_Session',
          'garbageCollection' => 
          array (
            'probability' => 1,
            'maximumPerRun' => 1000,
          ),
          'cookie' => 
          array (
            'lifetime' => 0,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'domain' => NULL,
          ),
        ),
        'utility' => 
        array (
          'lockStrategyClassName' => 'Neos\\Utility\\Lock\\FlockLockStrategy',
        ),
        'Log' => 
        array (
        ),
      ),
      'DocTools' => 
      array (
        'collections' => 
        array (
          'Flow' => 
          array (
            'commandReferences' => 
            array (
              0 => 'Flow:FlowCommands',
            ),
            'references' => 
            array (
              0 => 'TYPO3Fluid:ViewHelpers',
              1 => 'Flow:FluidAdaptorViewHelpers',
              2 => 'Flow:FlowValidators',
              3 => 'Flow:FlowSignals',
              4 => 'Flow:FlowTypeConverters',
              5 => 'Flow:FlowAnnotations',
            ),
          ),
        ),
        'commandReferences' => 
        array (
          'Flow:FlowCommands' => 
          array (
            'title' => 'Flow Command Reference',
            'packageKeys' => 
            array (
              0 => 'Neos.Flow',
              1 => 'Neos.Party',
              2 => 'Neos.FluidAdaptor',
              3 => 'Neos.Kickstart',
              4 => 'Neos.Welcome',
            ),
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/CommandReference.rst',
          ),
        ),
        'references' => 
        array (
          'TYPO3Fluid:ViewHelpers' => 
          array (
            'title' => 'TYPO3 Fluid ViewHelper Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/TYPO3FluidViewHelperReference.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractViewHelper',
              'classNamePattern' => '/^TYPO3Fluid\\\\Fluid\\\\ViewHelpers\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FluidViewHelperClassParser',
              'options' => 
              array (
                'namespaces' => 
                array (
                  'f' => 'TYPO3Fluid\\Fluid\\ViewHelpers',
                ),
              ),
            ),
          ),
          'Flow:FluidAdaptorViewHelpers' => 
          array (
            'title' => 'FluidAdaptor ViewHelper Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/FluidAdaptorViewHelperReference.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\FluidAdaptor\\Core\\ViewHelper\\AbstractViewHelper',
              'classNamePattern' => '/^Neos\\\\FluidAdaptor\\\\ViewHelpers\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FluidViewHelperClassParser',
              'options' => 
              array (
                'namespaces' => 
                array (
                  'f' => 'Neos\\FluidAdaptor\\ViewHelpers',
                ),
              ),
            ),
          ),
          'Flow:FlowValidators' => 
          array (
            'title' => 'Flow Validator Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/ValidatorReference.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\Flow\\Validation\\Validator\\AbstractValidator',
              'classNamePattern' => '/^Neos\\\\Flow\\\\Validation\\\\Validator\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FlowValidatorClassParser',
            ),
          ),
          'Flow:FlowSignals' => 
          array (
            'title' => 'Flow Signals Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/SignalsReference.rst',
            'affectedClasses' => 
            array (
              'classesContainingMethodsAnnotatedWith' => 'Neos\\Flow\\Annotations\\Signal',
              'classNamePattern' => '/^Neos\\\\Flow\\\\.*$/i',
              'includeAbstractClasses' => true,
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\SignalsParser',
            ),
          ),
          'Flow:FlowTypeConverters' => 
          array (
            'title' => 'Flow TypeConverter Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/TypeConverterReference.rst',
            'affectedClasses' => 
            array (
              'parentClassName' => 'Neos\\Flow\\Property\\TypeConverter\\AbstractTypeConverter',
              'classNamePattern' => '/^Neos\\\\Flow\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FlowTypeConverterClassParser',
            ),
          ),
          'Flow:FlowAnnotations' => 
          array (
            'title' => 'Flow Annotation Reference',
            'savePathAndFilename' => (defined('FLOW_PATH_PACKAGES') ? constant('FLOW_PATH_PACKAGES') : null) . 'Framework/Neos.Flow/Documentation/TheDefinitiveGuide/PartV/AnnotationReference.rst',
            'affectedClasses' => 
            array (
              'classNamePattern' => '/^Neos\\\\Flow\\\\Annotations\\\\.*$/i',
            ),
            'parser' => 
            array (
              'implementationClassName' => 'Neos\\DocTools\\Domain\\Service\\FlowAnnotationClassParser',
            ),
          ),
        ),
      ),
      'Utility' => 
      array (
        'Files' => 
        array (
        ),
        'Pdo' => 
        array (
        ),
        'OpcodeCache' => 
        array (
        ),
        'ObjectHandling' => 
        array (
        ),
        'Arrays' => 
        array (
        ),
        'MediaTypes' => 
        array (
        ),
        'Schema' => 
        array (
        ),
      ),
      'Cache' => 
      array (
      ),
      'Eel' => 
      array (
      ),
      'FluidAdaptor' => 
      array (
      ),
      'Welcome' => 
      array (
      ),
      'Behat' => 
      array (
      ),
      'Kickstarter' => 
      array (
      ),
    ),
    'neos' => 
    array (
      'errormessages' => 
      array (
      ),
      'utilityunicode' => 
      array (
      ),
      'composerplugin' => 
      array (
      ),
      'utilitylock' => 
      array (
      ),
    ),
    'typo3fluid' => 
    array (
      'fluid' => 
      array (
      ),
    ),
    'psr' => 
    array (
      'httpmessage' => 
      array (
      ),
      'log' => 
      array (
      ),
    ),
    'paragonie' => 
    array (
      'randomcompat' => 
      array (
      ),
    ),
    'ramsey' => 
    array (
      'uuid' => 
      array (
      ),
    ),
    'Doctrine' => 
    array (
      'Common' => 
      array (
        'Collections' => 
        array (
        ),
        'Lexer' => 
        array (
        ),
      ),
      'DBAL' => 
      array (
      ),
      'ORM' => 
      array (
      ),
    ),
    'doctrine' => 
    array (
      'inflector' => 
      array (
      ),
      'cache' => 
      array (
      ),
      'annotations' => 
      array (
      ),
      'common' => 
      array (
      ),
      'instantiator' => 
      array (
      ),
      'migrations' => 
      array (
      ),
    ),
    'symfony' => 
    array (
      'polyfillmbstring' => 
      array (
      ),
      'debug' => 
      array (
      ),
      'console' => 
      array (
      ),
      'yaml' => 
      array (
      ),
      'domcrawler' => 
      array (
      ),
    ),
    'zendframework' => 
    array (
      'zendeventmanager' => 
      array (
      ),
      'zendcode' => 
      array (
      ),
    ),
    'ocramius' => 
    array (
      'packageversions' => 
      array (
      ),
      'proxymanager' => 
      array (
      ),
    ),
    'org' => 
    array (
      'bovigo' => 
      array (
        'vfs' => 
        array (
        ),
      ),
    ),
    'phpunit' => 
    array (
      'phptokenstream' => 
      array (
      ),
      'phptexttemplate' => 
      array (
      ),
      'phpfileiterator' => 
      array (
      ),
      'phptimer' => 
      array (
      ),
      'phpcodecoverage' => 
      array (
      ),
      'phpunitmockobjects' => 
      array (
      ),
      'phpunit' => 
      array (
      ),
    ),
    'AgzHack' => 
    array (
      'LightMarkerImporter' => 
      array (
      ),
      'Api' => 
      array (
      ),
      'Auth' => 
      array (
      ),
      'Geo' => 
      array (
      ),
      'Lux' => 
      array (
      ),
      'KML' => 
      array (
      ),
    ),
    'myclabs' => 
    array (
      'deepcopy' => 
      array (
      ),
    ),
    'phpdocumentor' => 
    array (
      'reflectioncommon' => 
      array (
      ),
      'typeresolver' => 
      array (
      ),
      'reflectiondocblock' => 
      array (
      ),
    ),
    'webmozart' => 
    array (
      'assert' => 
      array (
      ),
    ),
    'sebastian' => 
    array (
      'diff' => 
      array (
      ),
      'recursioncontext' => 
      array (
      ),
      'exporter' => 
      array (
      ),
      'comparator' => 
      array (
      ),
      'codeunitreverselookup' => 
      array (
      ),
      'environment' => 
      array (
      ),
      'version' => 
      array (
      ),
      'globalstate' => 
      array (
      ),
      'objectreflector' => 
      array (
      ),
      'objectenumerator' => 
      array (
      ),
      'resourceoperations' => 
      array (
      ),
    ),
    'phpspec' => 
    array (
      'prophecy' => 
      array (
      ),
    ),
    'theseer' => 
    array (
      'tokenizer' => 
      array (
      ),
    ),
    'beberlei' => 
    array (
      'DoctrineExtensions' => 
      array (
      ),
    ),
    'nuovo' => 
    array (
      'spreadsheetreader' => 
      array (
      ),
    ),
  ),
  'Routes' => 
  array (
    0 => 
    array (
      'name' => 'AgzHack.Api :: Light resource',
      'uriPattern' => 'light-markers',
      'defaults' => 
      array (
        '@package' => 'AgzHack.Api',
        '@format' => 'html',
        '@controller' => 'LightMarkers',
        '@action' => 'index',
      ),
    ),
    1 => 
    array (
      'name' => 'AgzHack.Api :: User Auth',
      'uriPattern' => 'login',
      'defaults' => 
      array (
        '@package' => 'AgzHack.Api',
        '@format' => 'html',
        '@controller' => 'users',
        '@action' => 'login',
      ),
    ),
    2 => 
    array (
      'name' => 'Neos.Welcome :: Welcome screen',
      'uriPattern' => 'flow/welcome',
      'defaults' => 
      array (
        '@package' => 'Neos.Welcome',
        '@controller' => 'Standard',
        '@action' => 'index',
        '@format' => 'html',
      ),
    ),
    3 => 
    array (
      'name' => 'Neos.Welcome :: Redirect to welcome screen',
      'uriPattern' => '',
      'defaults' => 
      array (
        '@package' => 'Neos.Welcome',
        '@controller' => 'Standard',
        '@action' => 'redirect',
        '@format' => 'html',
      ),
    ),
  ),
  'Policy' => 
  array (
    'roles' => 
    array (
      'Neos.Flow:Everybody' => 
      array (
        'abstract' => true,
        'privileges' => 
        array (
          0 => 
          array (
            'privilegeTarget' => 'AgzHack.Api:UsersController.loginAction',
            'permission' => 'GRANT',
          ),
        ),
      ),
      'Neos.Flow:Anonymous' => 
      array (
        'abstract' => true,
      ),
      'Neos.Flow:AuthenticatedUser' => 
      array (
        'abstract' => true,
        'privileges' => 
        array (
          0 => 
          array (
            'privilegeTarget' => 'AgzHack.Api:LightMarkersController.allAction',
            'permission' => 'GRANT',
          ),
        ),
      ),
      'AgzHack.Auth:Customer' => 
      array (
        'privileges' => 
        array (
          0 => 
          array (
            'privilegeTarget' => 'AgzHack.Api:LightMarkersController.allAction',
            'permission' => 'GRANT',
          ),
        ),
      ),
      'AgzHack.Auth:Administrator' => 
      array (
        'privileges' => 
        array (
        ),
      ),
    ),
    'privilegeTargets' => 
    array (
      'Neos\\Flow\\Security\\Authorization\\Privilege\\Method\\MethodPrivilege' => 
      array (
        'AgzHack.Api:UsersController.loginAction' => 
        array (
          'matcher' => 'method(AgzHack\\Api\\Controller\\UsersController->loginAction())',
        ),
        'AgzHack.Api:LightMarkersController.allAction' => 
        array (
          'matcher' => 'method(AgzHack\\Api\\Controller\\LightMarkersController->.*())',
        ),
      ),
    ),
  ),
  'Views' => 
  array (
  ),
);