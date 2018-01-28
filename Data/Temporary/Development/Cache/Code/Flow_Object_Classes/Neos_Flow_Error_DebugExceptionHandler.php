<?php 
namespace Neos\Flow\Error;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Exception as FlowException;
use Neos\Flow\Http\Response;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;

/**
 * A basic but solid exception handler which catches everything which
 * falls through the other exception handlers and provides useful debugging
 * information.
 *
 * @Flow\Scope("singleton")
 */
class DebugExceptionHandler_Original extends AbstractExceptionHandler
{
    /**
     * The template for the HTML Exception output.
     *
     * @var string
     */
    protected $htmlExceptionTemplate = <<<'EOD'
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
    <head>
        <title>%s</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
        %s
        </style>
    </head>
    <body>
        %s
        <br />
        %s
        <br />
        %s
    </body>
</html>
EOD;

    /**
     * Formats and echoes the exception as XHTML.
     *
     * @param object $exception \Exception or \Throwable
     * @return void
     */
    protected function echoExceptionWeb($exception)
    {
        $statusCode = 500;
        if ($exception instanceof FlowException) {
            $statusCode = $exception->getStatusCode();
        }
        $statusMessage = Response::getStatusMessageByCode($statusCode);
        if (!headers_sent()) {
            header(sprintf('HTTP/1.1 %s %s', $statusCode, $statusMessage));
        }

        if (!isset($this->renderingOptions['templatePathAndFilename'])) {
            $this->renderStatically($statusCode, $exception);
            return;
        }

        try {
            echo $this->buildView($exception, $this->renderingOptions)->render();
        } catch (\Throwable $throwable) {
            $this->renderStatically($statusCode, $throwable);
        } catch (\Exception $exception) {
            $this->renderStatically($statusCode, $exception);
        }
    }

    /**
     * Returns the statically rendered exception message
     *
     * @param integer $statusCode
     * @param object $exception \Exception or \Throwable
     * @return void
     */
    protected function renderStatically($statusCode, $exception)
    {
        $statusMessage = Response::getStatusMessageByCode($statusCode);
        $exceptionHeader = '<div class="Flow-Debug-Exception-Header">';
        while (true) {
            $filepaths = Debugger::findProxyAndShortFilePath($exception->getFile());
            $filePathAndName = $filepaths['proxy'] !== '' ? $filepaths['proxy'] : $filepaths['short'];
            $exceptionMessageParts = $this->splitExceptionMessage($exception->getMessage());

            $exceptionHeader .= '<h1 class="ExceptionSubject">' . htmlspecialchars($exceptionMessageParts['subject']) . '</h1>';
            if ($exceptionMessageParts['body'] !== '') {
                $exceptionHeader .= '<p class="ExceptionBody">' . nl2br(htmlspecialchars($exceptionMessageParts['body'])) . '</p>';
            }

            $exceptionHeader .= '<table class="Flow-Debug-Exception-Meta"><tbody>';
            $exceptionHeader .= '<tr><th>Exception Code</th><td class="ExceptionProperty">' . $exception->getCode() . '</td></tr>';
            $exceptionHeader .= '<tr><th>Exception Type</th><td class="ExceptionProperty">' . get_class($exception) . '</td></tr>';
            if ($exception instanceof FlowException) {
                $exceptionHeader .= '<tr><th>Log Reference</th><td class="ExceptionProperty">' . $exception->getReferenceCode() . '</td></tr>';
            }

            $exceptionHeader .= '<tr><th>Thrown in File</th><td class="ExceptionProperty">' . $filePathAndName . '</td></tr>';
            $exceptionHeader .= '<tr><th>Line</th><td class="ExceptionProperty">' . $exception->getLine() . '</td></tr>';

            if ($filepaths['proxy'] !== '') {
                $exceptionHeader .= '<tr><th>Original File</th><td class="ExceptionProperty">' . $filepaths['short'] . '</td></tr>';
            }
            $exceptionHeader .= '</tbody></table>';

            if ($exception->getPrevious() === null) {
                break;
            }
            $exceptionHeader .= '<br /><h2>Nested Exception</h2>';
            $exception = $exception->getPrevious();
        }

        $exceptionHeader .= '</div>';

        $backtraceCode = Debugger::getBacktraceCode($exception->getTrace());

        $footer = '<div class="Flow-Debug-Exception-Footer">';
        $footer .= '<table class="Flow-Debug-Exception-InstanceData"><tbody>';
        if (defined('FLOW_PATH_ROOT')) {
            $footer .= '<tr><th>Instance root</th><td class="ExceptionProperty">' . FLOW_PATH_ROOT . '</td></tr>';
        }
        if (Bootstrap::$staticObjectManager instanceof ObjectManagerInterface) {
            $bootstrap = Bootstrap::$staticObjectManager->get(Bootstrap::class);
            $footer .= '<tr><th>Application Context</th><td class="ExceptionProperty">' . $bootstrap->getContext() . '</td></tr>';
            $footer .= '<tr><th>Request Handler</th><td class="ExceptionProperty">' . get_class($bootstrap->getActiveRequestHandler()) . '</td></tr>';
        }
        $footer .= '</tbody></table>';
        $footer .= '</div>';

        echo sprintf($this->htmlExceptionTemplate,
            $statusCode . ' ' . $statusMessage,
            file_get_contents(__DIR__ . '/../../Resources/Public/Error/Exception.css'),
            $exceptionHeader,
            $backtraceCode,
            $footer
        );
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Error;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A basic but solid exception handler which catches everything which
 * falls through the other exception handlers and provides useful debugging
 * information.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class DebugExceptionHandler extends DebugExceptionHandler_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\Error\DebugExceptionHandler') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Error\DebugExceptionHandler', $this);
        parent::__construct();
        if ('Neos\Flow\Error\DebugExceptionHandler' === get_class($this)) {
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
  'htmlExceptionTemplate' => 'string',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'options' => 'array',
  'renderingOptions' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Error\DebugExceptionHandler') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Error\DebugExceptionHandler', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->Flow_Injected_Properties = array (
  0 => 'systemLogger',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Error/DebugExceptionHandler.php
#