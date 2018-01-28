<?php 
namespace Neos\FluidAdaptor\ViewHelpers\Form;

/*
 * This file is part of the Neos.FluidAdaptor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\FluidAdaptor\ViewHelpers\FormViewHelper;

/**
 * A view helper which generates an <input type="file"> HTML element.
 * Make sure to set enctype="multipart/form-data" on the form!
 *
 * If a file has been uploaded successfully and the form is re-displayed due to validation errors,
 * this ViewHelper will render hidden fields that contain the previously generated resource so you
 * won't have to upload the file again.
 *
 * You can use a separate ViewHelper to display previously uploaded resources in order to remove/replace them.
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.upload name="file" />
 * </code>
 * <output>
 * <input type="file" name="file" />
 * </output>
 *
 * <code title="Multiple Uploads">
 * <f:form.upload property="attachments.0.originalResource" />
 * <f:form.upload property="attachments.1.originalResource" />
 * </code>
 * <output>
 * <input type="file" name="formObject[attachments][0][originalResource]">
 * <input type="file" name="formObject[attachments][0][originalResource]">
 * </output>
 *
 * <code title="Default resource">
 * <f:form.upload name="file" value="{someDefaultResource}" />
 * </code>
 * <output>
 * <input type="hidden" name="file[originallySubmittedResource][__identity]" value="<someDefaultResource-UUID>" />
 * <input type="file" name="file" />
 * </output>
 *
 * <code title="Specifying the resource collection for the new resource">
 * <f:form.upload name="file" collection="invoices"/>
 * </code>
 * <output>
 * <input type="file" name="yourInvoice" />
 * <input type="hidden" name="yourInvoice[__collectionName]" value="invoices" />
 * </output>
 *
 * @api
 */
class UploadViewHelper_Original extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', false, 'f3-form-error');
        $this->registerArgument('collection', 'string', 'Name of the resource collection this file should be uploaded to', false, '');
        $this->registerUniversalTagAttributes();
    }

    /**
     * Renders the upload field.
     *
     * @return string
     * @api
     */
    public function render()
    {
        $nameAttribute = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($nameAttribute);

        $output = '';
        $resource = $this->getUploadedResource();
        if ($resource !== null) {
            $resourceIdentityAttribute = '';
            if ($this->hasArgument('id')) {
                $resourceIdentityAttribute = ' id="' . htmlspecialchars($this->arguments['id']) . '-resource-identity"';
            }
            $output .= '<input type="hidden" name="'. htmlspecialchars($nameAttribute) . '[originallySubmittedResource][__identity]" value="' . $this->persistenceManager->getIdentifierByObject($resource) . '"' . $resourceIdentityAttribute . ' />';
        }

        if ($this->hasArgument('collection') && $this->arguments['collection'] !== false && $this->arguments['collection'] !== '') {
            $output .= '<input type="hidden" name="'. htmlspecialchars($nameAttribute) . '[__collectionName]" value="' . htmlspecialchars($this->arguments['collection']) . '" />';
        }

        $this->tag->addAttribute('type', 'file');
        $this->tag->addAttribute('name', $nameAttribute);

        $this->addAdditionalIdentityPropertiesIfNeeded();
        $this->setErrorClassAttribute();

        $output .= $this->tag->render();
        return $output;
    }

    /**
     * Returns a previously uploaded resource, or the resource specified via "value" argument if no resource has been uploaded before
     * If errors occurred during property mapping for this property, NULL is returned
     *
     * @return PersistentResource or NULL if no resource was uploaded and the "value" argument is not set
     */
    protected function getUploadedResource()
    {
        $resource = null;
        if ($this->hasMappingErrorOccurred()) {
            $resource = $this->getLastSubmittedFormData();
        } elseif ($this->hasArgument('value')) {
            $resource = $this->arguments['value'];
        } elseif ($this->isObjectAccessorMode()) {
            $resource = $this->getPropertyValue();
        }
        if ($resource === null) {
            return null;
        }
        if ($resource instanceof PersistentResource) {
            return $resource;
        }
        return $this->propertyMapper->convert($resource, PersistentResource::class);
    }

    /**
     * Get the name of this form element, without prefix.
     *
     * Note: This is overridden here because the "value" argument should not have an effect on the name attribute of the <input type="file" /> tag
     * In the original implementation, setting a value will influence the name, @see AbstractFormFieldViewHelper::getNameWithoutPrefix()
     *
     * @return string name
     */
    protected function getNameWithoutPrefix()
    {
        if ($this->isObjectAccessorMode()) {
            $propertySegments = explode('.', $this->arguments['property']);
            $formObjectName = $this->viewHelperVariableContainer->get(FormViewHelper::class, 'formObjectName');
            if (!empty($formObjectName)) {
                array_unshift($propertySegments, $formObjectName);
            }
            $name = array_shift($propertySegments);
            foreach ($propertySegments as $segment) {
                $name .= '[' . $segment . ']';
            }
        } else {
            $name = $this->hasArgument('name') ? $this->arguments['name'] : '';
        }

        return $name;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\ViewHelpers\Form;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A view helper which generates an <input type="file"> HTML element.
 * Make sure to set enctype="multipart/form-data" on the form!
 * 
 * If a file has been uploaded successfully and the form is re-displayed due to validation errors,
 * this ViewHelper will render hidden fields that contain the previously generated resource so you
 * won't have to upload the file again.
 * 
 * You can use a separate ViewHelper to display previously uploaded resources in order to remove/replace them.
 * 
 * = Examples =
 * 
 * <code title="Example">
 * <f:form.upload name="file" />
 * </code>
 * <output>
 * <input type="file" name="file" />
 * </output>
 * 
 * <code title="Multiple Uploads">
 * <f:form.upload property="attachments.0.originalResource" />
 * <f:form.upload property="attachments.1.originalResource" />
 * </code>
 * <output>
 * <input type="file" name="formObject[attachments][0][originalResource]">
 * <input type="file" name="formObject[attachments][0][originalResource]">
 * </output>
 * 
 * <code title="Default resource">
 * <f:form.upload name="file" value="{someDefaultResource}" />
 * </code>
 * <output>
 * <input type="hidden" name="file[originallySubmittedResource][__identity]" value="<someDefaultResource-UUID>" />
 * <input type="file" name="file" />
 * </output>
 * 
 * <code title="Specifying the resource collection for the new resource">
 * <f:form.upload name="file" collection="invoices"/>
 * </code>
 * <output>
 * <input type="file" name="yourInvoice" />
 * <input type="hidden" name="yourInvoice[__collectionName]" value="invoices" />
 * </output>
 */
class UploadViewHelper extends UploadViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        parent::__construct();
        if ('Neos\FluidAdaptor\ViewHelpers\Form\UploadViewHelper' === get_class($this)) {
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
  'tagName' => 'string',
  'propertyMapper' => 'Neos\\Flow\\Property\\PropertyMapper',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'escapeOutput' => 'boolean',
  'tag' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\TagBuilder',
  'controllerContext' => 'Neos\\Flow\\Mvc\\Controller\\ControllerContext',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'argumentDefinitions' => 'ArgumentDefinition[]',
  'viewHelperNode' => 'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode',
  'arguments' => 'array',
  'childNodes' => 'NodeInterface[] array',
  'templateVariableContainer' => 'TYPO3Fluid\\Fluid\\Core\\Variables\\VariableProviderInterface',
  'renderingContext' => 'TYPO3Fluid\\Fluid\\Core\\Rendering\\RenderingContextInterface',
  'renderChildrenClosure' => '\\Closure',
  'viewHelperVariableContainer' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer',
  'escapeChildren' => 'boolean',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectPersistenceManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'));
        $this->injectTagBuilder(new \TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder());
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Property\PropertyMapper', 'Neos\Flow\Property\PropertyMapper', 'propertyMapper', '2ab4a1ce2ee31715713d0f207f0ac637', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Property\PropertyMapper'); });
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
  1 => 'tagBuilder',
  2 => 'objectManager',
  3 => 'systemLogger',
  4 => 'propertyMapper',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.FluidAdaptor/Classes/ViewHelpers/Form/UploadViewHelper.php
#