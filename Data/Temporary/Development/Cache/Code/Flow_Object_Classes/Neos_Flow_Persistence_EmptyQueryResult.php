<?php 
namespace Neos\Flow\Persistence;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * An empty result list
 *
 * @api
 */
class EmptyQueryResult_Original implements QueryResultInterface
{
    /**
     * @var QueryInterface
     */
    protected $query;

    /**
     * Constructor
     *
     * @param QueryInterface $query
     */
    public function __construct(QueryInterface $query)
    {
        $this->query = $query;
    }

    /**
     * Returns a clone of the query object
     *
     * @return QueryInterface
     * @api
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Returns NULL
     *
     * @return object Returns NULL in this case
     * @api
     */
    public function getFirst()
    {
        return null;
    }

    /**
     * Returns an empty array
     *
     * @return array
     * @api
     */
    public function toArray()
    {
        return [];
    }

    /**
     * @return object Returns NULL in this case
     */
    public function current()
    {
        return null;
    }

    /**
     * @return void
     */
    public function next()
    {
    }

    /**
     * @return integer Returns 0 in this case
     */
    public function key()
    {
        return 0;
    }

    /**
     * @return boolean Returns FALSE in this case
     */
    public function valid()
    {
        return false;
    }

    /**
     * @return void
     */
    public function rewind()
    {
    }

    /**
     * @param mixed $offset
     * @return boolean Returns FALSE in this case
     */
    public function offsetExists($offset)
    {
        return false;
    }

    /**
     * @param mixed $offset
     * @return mixed Returns NULL in this case
     */
    public function offsetGet($offset)
    {
        return null;
    }

    /**
     * @param mixed $offset The offset is ignored in this case
     * @param mixed $value The value is ignored in this case
     * @return void
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * @param mixed $offset The offset is ignored in this case
     * @return void
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * @return integer Returns 0 in this case
     */
    public function count()
    {
        return 0;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Persistence;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An empty result list
 */
class EmptyQueryResult extends EmptyQueryResult_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param QueryInterface $query
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $query in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
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
  'query' => 'Neos\\Flow\\Persistence\\QueryInterface',
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
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Persistence/EmptyQueryResult.php
#