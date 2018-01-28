<?php 
namespace Neos\FluidAdaptor\ViewHelpers;

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
use Neos\Error\Messages\Message;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * View helper which renders the flash messages (if there are any) as an unsorted list.
 *
 * = Examples =
 *
 * <code title="Simple">
 * <f:flashMessages />
 * </code>
 * <output>
 * <ul>
 *   <li class="flashmessages-ok">Some Default Message</li>
 *   <li class="flashmessages-warning">Some Warning Message</li>
 * </ul>
 * </output>
 * Depending on the FlashMessages
 *
 * <code title="Output with css class">
 * <f:flashMessages class="specialClass" />
 * </code>
 * <output>
 * <ul class="specialClass">
 *   <li class="specialClass-ok">Default Message</li>
 *   <li class="specialClass-notice"><h3>Some notice message</h3>With message title</li>
 * </ul>
 * </output>
 *
 * <code title="Output flash messages as a list, with arguments and filtered by a severity">
 * <f:flashMessages severity="Warning" as="flashMessages">
 * 	<dl class="messages">
 * 	<f:for each="{flashMessages}" as="flashMessage">
 * 		<dt>{flashMessage.code}</dt>
 * 		<dd>{flashMessage}</dd>
 * 	</f:for>
 * 	</dl>
 * </f:flashMessages>
 * </code>
 * <output>
 * <dl class="messages">
 * 	<dt>1013</dt>
 * 	<dd>Some Warning Message.</dd>
 * </dl>
 * </output>
 *
 * @api
 */
class FlashMessagesViewHelper_Original extends AbstractTagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'ul';

    /**
     * Initialize arguments
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
    }

    /**
     * Renders flash messages that have been added to the FlashMessageContainer in previous request(s).
     *
     * @param string $as The name of the current flashMessage variable for rendering inside
     * @param string $severity severity of the messages (One of the \Neos\Error\Messages\Message::SEVERITY_* constants)
     * @return string rendered Flash Messages, if there are any.
     * @api
     */
    public function render($as = null, $severity = null)
    {
        $flashMessages = $this->controllerContext->getFlashMessageContainer()->getMessagesAndFlush($severity);
        if (count($flashMessages) < 1) {
            return '';
        }
        if ($as === null) {
            $content = $this->renderAsList($flashMessages);
        } else {
            $content = $this->renderFromTemplate($flashMessages, $as);
        }
        return $content;
    }

    /**
     * Render the flash messages as unsorted list. This is triggered if no "as" argument is given
     * to the ViewHelper.
     *
     * @param array<Message> $flashMessages
     * @return string
     */
    protected function renderAsList(array $flashMessages)
    {
        $flashMessagesClass = isset($this->arguments['class']) ? $this->arguments['class'] : 'flashmessages';
        $tagContent = '';
        /** @var $singleFlashMessage Message */
        foreach ($flashMessages as $singleFlashMessage) {
            $severityClass = sprintf('%s-%s', $flashMessagesClass, strtolower($singleFlashMessage->getSeverity()));
            $messageContent = htmlspecialchars($singleFlashMessage->render());
            if ($singleFlashMessage->hasTitle()) {
                $messageContent = sprintf('<h3>%s</h3>', htmlspecialchars($singleFlashMessage->getTitle())) . $messageContent;
            }
            $tagContent .= sprintf('<li class="%s">%s</li>', htmlspecialchars($severityClass), $messageContent);
        }
        $this->tag->setContent($tagContent);
        $content = $this->tag->render();

        return $content;
    }

    /**
     * Defer the rendering of Flash Messages to the template. In this case,
     * the flash messages are stored in the template inside the variable specified
     * in "as".
     *
     * @param array $flashMessages
     * @param string $as
     * @return string
     */
    protected function renderFromTemplate(array $flashMessages, $as)
    {
        $templateVariableContainer = $this->renderingContext->getVariableProvider();
        $templateVariableContainer->add($as, $flashMessages);
        $content = $this->renderChildren();
        $templateVariableContainer->remove($as);

        return $content;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\ViewHelpers;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * View helper which renders the flash messages (if there are any) as an unsorted list.
 * 
 * = Examples =
 * 
 * <code title="Simple">
 * <f:flashMessages />
 * </code>
 * <output>
 * <ul>
 *   <li class="flashmessages-ok">Some Default Message</li>
 *   <li class="flashmessages-warning">Some Warning Message</li>
 * </ul>
 * </output>
 * Depending on the FlashMessages
 * 
 * <code title="Output with css class">
 * <f:flashMessages class="specialClass" />
 * </code>
 * <output>
 * <ul class="specialClass">
 *   <li class="specialClass-ok">Default Message</li>
 *   <li class="specialClass-notice"><h3>Some notice message</h3>With message title</li>
 * </ul>
 * </output>
 * 
 * <code title="Output flash messages as a list, with arguments and filtered by a severity">
 * <f:flashMessages severity="Warning" as="flashMessages">
 * 	<dl class="messages">
 * 	<f:for each="{flashMessages}" as="flashMessage">
 * 		<dt>{flashMessage.code}</dt>
 * 		<dd>{flashMessage}</dd>
 * 	</f:for>
 * 	</dl>
 * </f:flashMessages>
 * </code>
 * <output>
 * <dl class="messages">
 * 	<dt>1013</dt>
 * 	<dd>Some Warning Message.</dd>
 * </dl>
 * </output>
 */
class FlashMessagesViewHelper extends FlashMessagesViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        parent::__construct();
        if ('Neos\FluidAdaptor\ViewHelpers\FlashMessagesViewHelper' === get_class($this)) {
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
        $this->injectTagBuilder(new \TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder());
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->Flow_Injected_Properties = array (
  0 => 'tagBuilder',
  1 => 'objectManager',
  2 => 'systemLogger',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.FluidAdaptor/Classes/ViewHelpers/FlashMessagesViewHelper.php
#