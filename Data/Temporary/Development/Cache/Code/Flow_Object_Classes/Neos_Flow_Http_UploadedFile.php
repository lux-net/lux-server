<?php 
namespace Neos\Flow\Http;

use InvalidArgumentException;
use Neos\Utility\Files;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * Generic implementation of the PSR-7 UploadedFileInterface.
 *
 * @api PSR-7
 */
class UploadedFile_Original implements UploadedFileInterface
{
    /**
     * @var string
     */
    protected $clientFilename;

    /**
     * @var string
     */
    protected $clientMediaType;

    /**
     * @var int
     */
    protected $error;

    /**
     * @var null|string
     */
    protected $file;

    /**
     * @var bool
     */
    protected $moved = false;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var StreamInterface|null
     */
    protected $stream;

    /**
     * @param StreamInterface|string|resource $streamOrFile
     * @param int $size
     * @param int $errorStatus
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct($streamOrFile, $size, $errorStatus, $clientFilename = null, $clientMediaType = null)
    {
        $this->error = $errorStatus;
        $this->size = $size;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;

        if ($this->isOk()) {
            $this->setStreamOrFile($streamOrFile);
        }
    }

    /**
     * Depending on the value set file or stream variable
     *
     * @param string|StreamInterface|resource $streamOrFile
     * @throws InvalidArgumentException
     */
    protected function setStreamOrFile($streamOrFile)
    {
        if (is_string($streamOrFile) || $streamOrFile instanceof StreamInterface) {
            $this->file = $streamOrFile;
            return;
        }

        if (is_resource($streamOrFile)) {
            $this->stream = new ContentStream($streamOrFile);
            return;
        }

        throw new InvalidArgumentException('Invalid stream or file provided for UploadedFile', 1490139592);
    }

    /**
     * Return true if there is no upload error
     *
     * @return boolean
     */
    protected function isOk()
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * @return boolean
     */
    public function isMoved()
    {
        return $this->moved;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @throws RuntimeException if the upload was not successful.
     * @api PSR-7
     */
    public function getStream()
    {
        $this->throwExceptionIfNotAccessible();

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        return new ContentStream($this->file, 'r+');
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws RuntimeException if the upload was not successful.
     * @throws InvalidArgumentException if the $path specified is invalid.
     * @throws RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     * @api PSR-7
     */
    public function moveTo($targetPath)
    {
        $this->throwExceptionIfNotAccessible();

        if (!is_string($targetPath) || empty($targetPath)) {
            throw new InvalidArgumentException('Invalid path provided to move uploaded file to. Must be a non-empty string', 1479747624);
        }

        if ($this->stream !== null || ($this->file !== null && FLOW_SAPITYPE === 'CLI')) {
            $this->moved = $this->writeFile($targetPath);
        }

        if ($this->file !== null && FLOW_SAPITYPE !== 'CLI') {
            $this->moved = move_uploaded_file($this->file, $targetPath);
        }

        if ($this->moved === false) {
            throw new RuntimeException(sprintf('Uploaded file could not be moved to %s', $targetPath), 1479747889);
        }
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     * @api PSR-7
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     * @api PSR-7
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     * @api PSR-7
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @api PSR-7
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    /**
     * @throws RuntimeException if is moved or not ok
     */
    protected function throwExceptionIfNotAccessible()
    {
        if (!$this->isOk()) {
            throw new RuntimeException('UploadedFile has the following error: ' . Files::getUploadErrorMessage($this->error), 1479743608);
        }

        if ($this->isMoved()) {
            throw new RuntimeException('The uploaded file was moved already and cannot be accessed anymore.', 1479743612);
        }
    }

    /**
     * Write the uploaded file to the given path.
     *
     * @param string $path
     * @return boolean
     */
    protected function writeFile($path)
    {
        $handle = fopen($path, 'wb+');
        if ($handle === false) {
            return false;
        }

        $stream = $this->getStream();
        $stream->rewind();
        while (!$stream->eof()) {
            fwrite($handle, $stream->read(4096));
        }

        fclose($handle);
        return true;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Http;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Generic implementation of the PSR-7 UploadedFileInterface.
 */
class UploadedFile extends UploadedFile_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param StreamInterface|string|resource $streamOrFile
     * @param int $size
     * @param int $errorStatus
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $streamOrFile in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $size in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $errorStatus in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'clientFilename' => 'string',
  'clientMediaType' => 'string',
  'error' => 'integer',
  'file' => 'null|string',
  'moved' => 'boolean',
  'size' => 'integer',
  'stream' => 'StreamInterface|null',
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
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Http/UploadedFile.php
#