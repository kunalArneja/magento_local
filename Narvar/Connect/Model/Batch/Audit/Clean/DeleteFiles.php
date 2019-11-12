<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 16.05.18
 * Time: 16:36
 */

namespace Narvar\Connect\Model\Batch\Audit\Clean;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\DateTime;

class DeleteFiles
{

    /**
     * Constant for getting all files in directory
     */
    const ALL_FILES_IN_DIR_PATTERN = "/*";

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var string
     */
    protected $fileNamePattern;

    /**
     * @var int
     */
    protected $lifeTime;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * DeleteFiles constructor.
     * @param DirectoryList $directoryList
     * @param DateTime $dateTime
     */
    public function __construct(
        DirectoryList $directoryList,
        DateTime $dateTime
    ) {
        $this->directoryList = $directoryList;
        $this->dateTime = $dateTime;
    }

    /**
     * @param string $auditCleanInterval
     * @param int $storeId
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function process($auditCleanInterval,  $storeId)
    {
        $commonFilePath = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . 'narvar' . DIRECTORY_SEPARATOR . 'import';
        $this->fileNamePattern = $storeId . '_store';
        $this->lifeTime = $this->dateTime->timestamp() - ($auditCleanInterval * 24 * 60 * 60);

        $this->delete($commonFilePath);
    }

    /**
     * @param $dirName
     */
    protected function delete($dirName)
    {
        $subDir = glob($dirName . self::ALL_FILES_IN_DIR_PATTERN);

        foreach ($subDir as $item) {
            if (is_dir($item)) {
                $this->delete($item);

                if (file_exists($item) && empty(glob($item . self::ALL_FILES_IN_DIR_PATTERN))) {
                    rmdir($item);
                }
            } else {
                if (file_exists($item)) {
                    if (filemtime($item) <= $this->lifeTime && strpos($item, $this->fileNamePattern)) {
                        unlink($item);
                    }
                }
            }
        }
    }

}