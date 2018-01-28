<?php
namespace AgzHack\Auth\EntryPoint;

use Neos\Flow\Http\Request;
use Neos\Flow\Http\Response;
use Neos\Flow\Security\Authentication\EntryPoint\AbstractEntryPoint;

/**
 * An authentication entry point, that sends an HTTP header to start HTTP Basic authentication.
 */
class StatelessToken extends AbstractEntryPoint
{

    /**
     * @param Request $request
     * @param Response $response
     */
    public function startAuthentication(Request $request, Response $response)
    {
        $response->setStatus(401);
    }
}
