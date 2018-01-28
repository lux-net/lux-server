<?php 
namespace Neos\Flow\Http;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\SystemLoggerInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Implementation of a PSR-7 HTTP stream
 *
 * @api PSR-7
 */
class ContentStream_Original implements StreamInterface
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var string|resource
     */
    protected $stream;

    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @param string|resource $stream
     * @param string $mode Mode with which to open stream
     * @throws \InvalidArgumentException
     */
    public function __construct($stream, $mode = 'r')
    {
        $this->replace($stream, $mode);
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        if (!$this->resource) {
            return;
        }

        $resource = $this->detach();
        fclose($resource);
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;

        return $resource;
    }

    /**
     * Attach a new stream/resource to the instance.
     *
     * @param string|resource $stream
     * @param string $mode
     */
    public function replace($stream, $mode = 'r')
    {
        $this->close();
        if (!is_resource($stream)) {
            $stream = $this->openStream($stream, $mode);
        }

        if (!$this->isValidResource($stream)) {
            throw new \InvalidArgumentException('Invalid stream provided; must be a string or stream resource', 1453891861);
        }

        $this->resource = $stream;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        if (!$this->isValidResource($this->resource)) {
            return null;
        }

        $stats = fstat($this->resource);

        return $stats['size'];
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        $this->ensureResourceOpen();

        $result = ftell($this->resource);
        if (!is_int($result)) {
            throw new \RuntimeException('Error occurred during tell operation', 1453892219);
        }

        return $result;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        if (!$this->isValidResource($this->resource)) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        if (!$this->isValidResource($this->resource)) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);

        return $meta['seekable'];
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @return bool
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->ensureResourceOpen();

        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable', 1453892227);
        }

        $result = fseek($this->resource, $offset, $whence);

        if (0 !== $result) {
            throw new \RuntimeException('Error seeking within stream', 1453892231);
        }

        return true;
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        return $this->seek(0);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        if (!$this->isValidResource($this->resource)) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return (
            strstr($mode, 'x')
            || strstr($mode, 'w')
            || strstr($mode, 'c')
            || strstr($mode, 'a')
            || strstr($mode, '+')
        );
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string)
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable', 1453892241);
        }

        $result = fwrite($this->resource, $string);

        if (false === $result) {
            throw new \RuntimeException('Error writing to stream', 1453892247);
        }

        return $result;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        if (!$this->isValidResource($this->resource)) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return (strstr($mode, 'r') || strstr($mode, '+'));
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        $this->ensureResourceReadable();

        $result = fread($this->resource, $length);

        if ($result === false) {
            throw new \RuntimeException('Error reading stream', 1453892260);
        }

        return $result;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents()
    {
        $this->ensureResourceReadable();

        $result = stream_get_contents($this->resource);
        if ($result === false) {
            throw new \RuntimeException('Error reading from stream', 1453892266);
        }

        return $result;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return stream_get_meta_data($this->resource);
        }

        $metadata = stream_get_meta_data($this->resource);
        if (!array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[$key];
    }

    /**
     * Set the internal stream resource.
     *
     * @param string $stream String stream target or stream resource.
     * @param string $mode Resource mode for stream target.
     * @return resource
     */
    protected function openStream($stream, $mode = 'r')
    {
        $resource = fopen($stream, $mode);
        return $resource;
    }

    /**
     * Throw an exception if the current resource is not readable.
     */
    protected function ensureResourceReadable()
    {
        if ($this->isReadable() === false) {
            throw new \RuntimeException('Stream is not readable.', 1453892039);
        }
    }

    /**
     * Throw an exception if the current resource is not valid.
     */
    protected function ensureResourceOpen()
    {
        if (!$this->isValidResource($this->resource)) {
            throw new \RuntimeException('No resource available to apply operation', 1453891806);
        }
    }

    /**
     * @return boolean
     */
    protected function isValidResource($resource)
    {
        return (is_resource($resource) && get_resource_type($resource) === 'stream');
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            $this->rewind();

            return $this->getContents();
        } catch (\Exception $e) {
            $this->systemLogger->log(sprintf('Tried to convert a http content stream to a string but an exception occured: [%s] - %s', $e->getCode(), $e->getMessage()), LOG_ERR);
            return '';
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Http;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Implementation of a PSR-7 HTTP stream
 */
class ContentStream extends ContentStream_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param string|resource $stream
     * @param string $mode Mode with which to open stream
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $stream in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Http\ContentStream' === get_class($this)) {
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
  'resource' => 'resource',
  'stream' => 'string|resource',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'systemLogger',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Http/ContentStream.php
#