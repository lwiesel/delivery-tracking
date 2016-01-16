<?php

namespace LWI\DeliveryTracking\Adapter;

abstract class AbstractFtpAdapter
{
    /**
     * @const  VISIBILITY_PUBLIC  public visibility
     */
    const VISIBILITY_PUBLIC = 'public';
    /**
     * @const  VISIBILITY_PRIVATE  private visibility
     */
    const VISIBILITY_PRIVATE = 'private';

    /**
     * @var array
     */
    protected $configurable = [
        'host',
        'port',
        'username',
        'password',
        'ssl',
        'timeout',
        'root',
        'permPrivate',
        'permPublic',
        'passive',
        'transferMode',
        'systemType',
    ];

    /**
     * @var mixed
     */
    protected $connection;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port = 21;

    /**
     * @var string|null
     */
    protected $username;

    /**
     * @var string|null
     */
    protected $password;

    /**
     * @var bool
     */
    protected $ssl = false;

    /**
     * @var int
     */
    protected $timeout = 90;

    /**
     * @var bool
     */
    protected $passive = true;

    /**
     * @var int
     */
    protected $transferMode = FTP_BINARY;

    /**
     * @var string
     */
    protected $separator = '/';

    /**
     * @var string|null
     */
    protected $root;

    /**
     * @var int
     */
    protected $permPublic = 0744;

    /**
     * @var int
     */
    protected $permPrivate = 0700;

    /**
     * @var string
     */
    protected $systemType;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * Disconnect on destruction.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Set the config.
     *
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        foreach ($this->configurable as $setting) {
            if (! isset($config[$setting])) {
                continue;
            }

            $method = 'set'.ucfirst($setting);

            if (method_exists($this, $method)) {
                $this->$method($config[$setting]);
            }
        }
        return $this;
    }

    /**
     * Connect to the FTP server.
     */
    public function connect()
    {
        if ($this->ssl) {
            $this->connection = ftp_ssl_connect($this->getHost(), $this->getPort(), $this->getTimeout());
        } else {
            $this->connection = ftp_connect($this->getHost(), $this->getPort(), $this->getTimeout());
        }
        if (!$this->connection) {
            throw new \RuntimeException(
                'Could not connect to host: ' . $this->getHost() . ', port:' . $this->getPort()
            );
        }
        $this->login();
        $this->setConnectionPassiveMode();
        $this->setConnectionRoot();
    }

    /**
     * Set the connections to passive mode.
     *
     * @throws \RuntimeException
     */
    protected function setConnectionPassiveMode()
    {
        if (!ftp_pasv($this->connection, $this->passive)) {
            throw new \RuntimeException(
                'Could not set passive mode for connection: ' . $this->getHost() . '::' . $this->getPort()
            );
        }
    }
    /**
     * Set the connection root.
     */
    protected function setConnectionRoot()
    {
        $root = $this->getRoot();
        $connection = $this->connection;
        if (empty($root) === false && ! ftp_chdir($connection, $root)) {
            throw new \RuntimeException('Root is invalid or does not exist: ' . $this->getRoot());
        }
        // Store absolute path for further reference.
        // This is needed when creating directories and
        // initial root was a relative path, else the root
        // would be relative to the chdir'd path.
        $this->root = ftp_pwd($connection);
    }
    /**
     * Login.
     *
     * @throws \RuntimeException
     */
    protected function login()
    {
        set_error_handler(
            function () {
            }
        );
        $isLoggedIn = ftp_login($this->connection, $this->getUsername(), $this->getPassword());
        restore_error_handler();
        if (!$isLoggedIn) {
            $this->disconnect();
            throw new \RuntimeException(
                'Could not login with connection: ' . $this->getHost() . '::' . $this->getPort(
                ) . ', username: ' . $this->getUsername()
            );
        }
    }

    /**
     * Disconnect from the FTP server.
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            ftp_close($this->connection);
        }
        $this->connection = null;
    }

    /**
     * @inheritdoc
     */
    public function read($path)
    {
        if (!$object = $this->readStream($path)) {
            return false;
        }
        $object['contents'] = stream_get_contents($object['stream']);
        fclose($object['stream']);
        unset($object['stream']);
        return $object;
    }
    /**
     * @inheritdoc
     */
    public function readStream($path)
    {
        $stream = fopen('php://temp', 'w+');
        $result = ftp_fget($this->getConnection(), $stream, $path, $this->transferMode);
        rewind($stream);
        if (!$result) {
            fclose($stream);
            return false;
        }
        return compact('stream');
    }

    /**
     * @inheritdoc
     *
     * @param string $directory
     */
    protected function listDirectoryContents($directory, $recursive = true)
    {
        $listing = ftp_rawlist($this->getConnection(), '-lna ' . $directory, $recursive);
        return $listing ? $this->normalizeListing($listing, $directory) : [];
    }
    /**
     * Check if the connection is open.
     *
     * @return bool
     */
    public function isConnected()
    {
        return ! is_null($this->connection) && ftp_systype($this->connection) !== false;
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        if (!$this->isConnected()) {
            $this->disconnect();
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Set the host.
     *
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Returns the host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the ftp port.
     *
     * @param int|string $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = (int) $port;
        return $this;
    }

    /**
     * Returns the ftp port.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set ftp username.
     *
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Returns the ftp username.
     *
     * @return string username
     */
    public function getUsername()
    {
        return empty($this->username) ? 'anonymous' : $this->username;
    }

    /**
     * Set the ftp password.
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Returns the password.
     *
     * @return string password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set if Ssl is enabled.
     *
     * @param bool $ssl
     *
     * @return $this
     */
    public function setSsl($ssl)
    {
        $this->ssl = (bool) $ssl;
        return $this;
    }

    /**
     * Get if ssl is enabled
     *
     * @return boolean
     */
    public function isSsl()
    {
        return $this->ssl;
    }

    /**
     * Set the amount of seconds before the connection should timeout.
     *
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;
        return $this;
    }

    /**
     * Returns the amount of seconds before the connection will timeout.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set if passive is enabled
     *
     * @param boolean $passive
     */
    public function setPassive($passive)
    {
        $this->passive = $passive;
    }

    /**
     * Get if passive is enabled
     *
     * @return boolean
     */
    public function isPassive()
    {
        return $this->passive;
    }

    /**
     * Set the transfer mode
     *
     * @param int $transferMode
     */
    public function setTransferMode($transferMode)
    {
        $this->transferMode = $transferMode;
    }

    /**
     * Get the transfer mode
     *
     * @return int
     */
    public function getTransferMode()
    {
        return $this->transferMode;
    }

    /**
     * Set the separator used
     *
     * @param string $separator
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    /**
     * Returns the separator used
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Set the root folder to work from.
     *
     * @param string $root
     *
     * @return $this
     */
    public function setRoot($root)
    {
        $this->root = rtrim($root, '\\/').$this->separator;
        return $this;
    }

    /**
     * Returns the root folder to work from.
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set the public permission value.
     *
     * @param int $permPublic
     *
     * @return $this
     */
    public function setPermPublic($permPublic)
    {
        $this->permPublic = $permPublic;
        return $this;
    }

    /**
     * Get the public permission value.
     *
     * @return int
     */
    public function getPermPublic()
    {
        return $this->permPublic;
    }

    /**
     * Set the private permission value.
     *
     * @param int $permPrivate
     *
     * @return $this
     */
    public function setPermPrivate($permPrivate)
    {
        $this->permPrivate = $permPrivate;
        return $this;
    }

    /**
     * Get the private permission value.
     *
     * @return int
     */
    public function getPermPrivate()
    {
        return $this->permPrivate;
    }

    /**
     * Set the FTP system type (windows or unix).
     *
     * @param string $systemType
     *
     * @return $this
     */
    public function setSystemType($systemType)
    {
        $this->systemType = strtolower($systemType);
        return $this;
    }

    /**
     * Return the FTP system type.
     *
     * @return string
     */
    public function getSystemType()
    {
        return $this->systemType;
    }

    /**
     * Normalize a directory listing.
     *
     * @param array  $listing
     * @param string $prefix
     *
     * @return array directory listing
     */
    protected function normalizeListing(array $listing, $prefix = '')
    {
        $base = $prefix;
        $result = [];
        $listing = $this->removeDotDirectories($listing);
        while ($item = array_shift($listing)) {
            if (preg_match('#^.*:$#', $item)) {
                $base = trim($item, ':');
                continue;
            }
            $result[] = $this->normalizeObject($item, $base);
        }
        return $this->sortListing($result);
    }

    /**
     * Sort a directory listing.
     *
     * @param array $result
     *
     * @return array sorted listing
     */
    protected function sortListing(array $result)
    {
        $compare = function ($one, $two) {
            return strnatcmp($one['path'], $two['path']);
        };
        usort($result, $compare);
        return $result;
    }

    /**
     * Normalize a file entry.
     *
     * @param string $item
     * @param string $base
     * @return array normalized file array
     *
     * @throws \Exception
     */
    protected function normalizeObject($item, $base)
    {
        $systemType = $this->systemType ?: $this->detectSystemType($item);
        if ($systemType === 'unix') {
            return $this->normalizeUnixObject($item, $base);
        } elseif ($systemType === 'windows') {
            return $this->normalizeWindowsObject($item, $base);
        }
        throw new \Exception('Unsupported system type.');
    }

    /**
     * Normalize a Unix file entry.
     *
     * @param string $item
     * @param string $base
     *
     * @return array normalized file array
     */
    protected function normalizeUnixObject($item, $base)
    {
        $item = preg_replace('#\s+#', ' ', trim($item), 7);
        list(
            $permissions,
            /* $number */,
            /* $owner */,
            /* $group */,
            $size,
            $month,
            $day,
            $time,
            $name
        ) = explode(' ', $item, 9);

        $type = $this->detectType($permissions);
        $path = empty($base) ? $name : $base.$this->separator.$name;
        if ($type === 'dir') {
            return compact('type', 'path');
        }
        $permissions = $this->normalizePermissions($permissions);
        $visibility = $permissions & 0044 ? self::VISIBILITY_PUBLIC : self::VISIBILITY_PRIVATE;
        $size = (int) $size;

        $date = \DateTime::createFromFormat(
            'Y-M-d H:i',
            '2015-'.$month.'-'.$day.' '.$time,
            new \DateTimeZone('Europe/Paris')
        );

        return compact('type', 'path', 'visibility', 'size', 'date');
    }

    /**
     * Normalize a Windows/DOS file entry.
     *
     * @param string $item
     * @param string $base
     *
     * @return array normalized file array
     */
    protected function normalizeWindowsObject($item, $base)
    {
        $item = preg_replace('#\s+#', ' ', trim($item), 3);
        list($date, $time, $size, $name) = explode(' ', $item, 4);
        $path = empty($base) ? $name : $base.$this->separator.$name;
        // Check for the correct date/time format
        $format = strlen($date) === 8 ? 'm-d-yH:iA' : 'Y-m-dH:i';
        $dateTime = \DateTime::createFromFormat($format, $date.$time)->getTimestamp();
        if ($size === '<DIR>') {
            $type = 'dir';
            return compact('type', 'path', 'dateTime');
        }
        $type = 'file';
        $visibility = self::VISIBILITY_PUBLIC;
        $size = (int) $size;
        return compact('type', 'path', 'visibility', 'size', 'dateTime');
    }

    /**
     * Get the system type from a listing item.
     *
     * @param string $item
     *
     * @return string the system type
     */
    protected function detectSystemType($item)
    {
        if (preg_match('/^[0-9]{2,4}-[0-9]{2}-[0-9]{2}/', $item)) {
            return $this->systemType = 'windows';
        }
        return $this->systemType = 'unix';
    }

    /**
     * Get the file type from the permissions.
     *
     * @param string $permissions
     *
     * @return string file type
     */
    protected function detectType($permissions)
    {
        return substr($permissions, 0, 1) === 'd' ? 'dir' : 'file';
    }

    /**
     * Normalize a permissions string.
     *
     * @param string $permissions
     *
     * @return int
     */
    protected function normalizePermissions($permissions)
    {
        // remove the type identifier
        $permissions = substr($permissions, 1);
        // map the string rights to the numeric counterparts
        $map = ['-' => '0', 'r' => '4', 'w' => '2', 'x' => '1'];
        $permissions = strtr($permissions, $map);
        // split up the permission groups
        $parts = str_split($permissions, 3);
        // convert the groups
        $mapper = function ($part) {
            return array_sum(str_split($part));
        };
        // get the sum of the groups
        return array_sum(array_map($mapper, $parts));
    }

    /**
     * Filter out dot-directories.
     *
     * @param array $list
     *
     * @return array
     */
    public function removeDotDirectories(array $list)
    {
        $filter = function ($line) {
            if (! empty($line) && !preg_match('#.* \.(\.)?$|^total#', $line)) {
                return true;
            }
            return false;
        };
        return array_filter($list, $filter);
    }
}
