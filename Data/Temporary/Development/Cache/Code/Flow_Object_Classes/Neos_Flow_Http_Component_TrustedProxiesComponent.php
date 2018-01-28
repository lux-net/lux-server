<?php 
namespace Neos\Flow\Http\Component;

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
use Neos\Flow\Http\Request;
use Neos\Flow\Utility\Ip as IpUtility;

/**
 * HTTP component that checks request headers against a configured list of trusted proxy IP addresses.
 */
class TrustedProxiesComponent_Original implements ComponentInterface
{
    const HEADER_CLIENT_IP = 'clientIp';
    const HEADER_HOST = 'host';
    const HEADER_PORT = 'port';
    const HEADER_PROTOCOL = 'proto';

    /**
     * @Flow\InjectConfiguration("http.trustedProxies")
     * @var array
     */
    protected $settings;

    /**
     * @param ComponentContext $componentContext
     * @return void
     * @api
     */
    public function handle(ComponentContext $componentContext)
    {
        $request = $componentContext->getHttpRequest();

        $trustedRequest = $request->withAttribute(Request::ATTRIBUTE_TRUSTED_PROXY, $this->isFromTrustedProxy($request));

        $trustedRequest = $trustedRequest->withAttribute(Request::ATTRIBUTE_CLIENT_IP, $this->getTrustedClientIpAddress($trustedRequest));

        $protocolHeader = $this->getFirstTrustedProxyHeaderValue(self::HEADER_PROTOCOL, $trustedRequest);
        if ($protocolHeader !== null) {
            $trustedRequest->getUri()->setScheme($protocolHeader);
        }

        $hostHeader = $this->getFirstTrustedProxyHeaderValue(self::HEADER_HOST, $trustedRequest);
        $portFromHost = null;
        if ($hostHeader !== null) {
            $portSeparatorIndex = strrpos($hostHeader, ':');
            if ($portSeparatorIndex !== false) {
                $portFromHost = substr($hostHeader, $portSeparatorIndex + 1);
                $trustedRequest->getUri()->setPort($portFromHost);
                $hostHeader = substr($hostHeader, 0, $portSeparatorIndex);
            }
            $trustedRequest->getUri()->setHost($hostHeader);
        }

        $portHeader = $this->getFirstTrustedProxyHeaderValue(self::HEADER_PORT, $trustedRequest);
        if ($portHeader !== null) {
            $trustedRequest->getUri()->setPort($portHeader);
        } elseif ($protocolHeader !== null && $portFromHost === null) {
            $trustedRequest->getUri()->setPort(strtolower($protocolHeader) === 'https' ? 443 : 80);
        }

        $componentContext->replaceHttpRequest($trustedRequest);
    }

    /**
     * Get the values of trusted proxy header.
     *
     * @param string $type One of the HEADER_* constants
     * @param Request $request The request to get the trusted proxy header from
     * @return \Iterator An array of the values for this header type or NULL if this header type should not be trusted
     */
    protected function getTrustedProxyHeaderValues($type, Request $request)
    {
        $trustedHeaders = isset($this->settings['headers'][$type]) ? $this->settings['headers'][$type] : '';
        if ($trustedHeaders === '' || !$request->getAttribute(Request::ATTRIBUTE_TRUSTED_PROXY)) {
            yield null;
            return;
        }
        $trustedHeaders = array_map('trim', explode(',', $trustedHeaders));

        foreach ($trustedHeaders as $trustedHeader) {
            if ($request->hasHeader($trustedHeader)) {
                yield array_map('trim', explode(',', $request->getHeader($trustedHeader)));
            }
        }

        yield null;
    }

    /**
     * Convenience getter for the first value of a given trusted proxy header.
     *
     * @param string $type One of the HEADER_* constants
     * @param Request $request The request to get the trusted proxy header from
     * @return mixed|null The first value of this header type or NULL if this header type should not be trusted
     */
    protected function getFirstTrustedProxyHeaderValue($type, Request $request)
    {
        $values = $this->getTrustedProxyHeaderValues($type, $request)->current();
        return $values !== null ? reset($values) : null;
    }

    /**
     * Check if the given IP address is from a trusted proxy.
     *
     * @param string $ipAddress
     * @return bool
     */
    protected function ipIsTrustedProxy($ipAddress)
    {
        if (filter_var($ipAddress, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        if ($this->settings['proxies'] === '*') {
            return true;
        }
        foreach ($this->settings['proxies'] as $ipPattern) {
            if (IpUtility::cidrMatch($ipAddress, $ipPattern)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the given request is from a trusted proxy.
     *
     * @param Request $request
     * @return bool If the server REMOTE_ADDR is from a trusted proxy
     */
    protected function isFromTrustedProxy(Request $request)
    {
        $server = $request->getServerParams();
        if (!isset($server['REMOTE_ADDR'])) {
            return false;
        }
        return $this->ipIsTrustedProxy($server['REMOTE_ADDR']);
    }

    /**
     * Get the most trusted client's IP address.
     *
     * This is the right-most address in the trusted client IP header, that is not a trusted proxy address.
     * If all proxies are trusted, this is the left-most address in the header.
     * If no proxies are trusted or no client IP header is trusted, this is the remote address of the machine
     * directly connected to the server.
     *
     * @return string|bool The most trusted client's IP address or FALSE if no remote address can be found
     */
    protected function getTrustedClientIpAddress(Request $request)
    {
        $server = $request->getServerParams();
        if (!isset($server['REMOTE_ADDR'])) {
            return false;
        }

        $ipAddress = $server['REMOTE_ADDR'];
        $trustedIpHeaders = $this->getTrustedProxyHeaderValues(self::HEADER_CLIENT_IP, $request);
        $trustedIpHeader = [];
        while ($trustedIpHeaders->valid()) {
            $trustedIpHeader = $trustedIpHeaders->current();
            if ($trustedIpHeader === null || $this->settings['proxies'] === []) {
                return $server['REMOTE_ADDR'];
            }
            $ipAddress = reset($trustedIpHeader);
            if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE) !== false) {
                break;
            }
            $trustedIpHeaders->next();
        }

        if ($this->settings['proxies'] === '*') {
            return $ipAddress;
        }

        $ipAddress = false;
        foreach (array_reverse($trustedIpHeader) as $headerIpAddress) {
            $portPosition = strpos($headerIpAddress, ':');
            $ipAddress = $portPosition !== false ? substr($headerIpAddress, 0, $portPosition) : $headerIpAddress;
            if (!$this->ipIsTrustedProxy($ipAddress)) {
                break;
            }
        }

        return $ipAddress;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Http\Component;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * HTTP component that checks request headers against a configured list of trusted proxy IP addresses.
 */
class TrustedProxiesComponent extends TrustedProxiesComponent_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Flow\Http\Component\TrustedProxiesComponent' === get_class($this)) {
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
  'settings' => 'array',
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
        $this->settings = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.http.trustedProxies');
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Http/Component/TrustedProxiesComponent.php
#