<?php

declare(strict_types=1);

namespace Mirakl\Mci\Model\Product\Import\Handler;

use Mirakl\Core\Helper\Csv as CsvHelper;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory;

class Path
{
    public const IMPORT_PROCESSING_DIR = 'processing';
    public const IMPORT_ARCHIVE_DIR    = 'archive';

    /**
     * @var CsvHelper
     */
    protected $csvHelper;

    /**
     * @var ProcessFactory
     */
    protected $processFactory;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @param CsvHelper      $csvHelper
     * @param ProcessFactory $processFactory
     * @param ProcessHelper  $processHelper
     */
    public function __construct(
        CsvHelper $csvHelper,
        ProcessFactory $processFactory,
        ProcessHelper $processHelper
    ) {
        $this->csvHelper = $csvHelper;
        $this->processFactory = $processFactory;
        $this->processHelper = $processHelper;
    }

    /**
     * Imports all CSV files present in the configured path
     *
     * @param string $path
     * @param int    $maxFiles
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function run($path, $maxFiles)
    {
        $DS = DIRECTORY_SEPARATOR;
        $createPathIfNotExists = function ($path) use ($DS) {
            if (!is_dir($path)) {
                @mkdir($path, 0777, true); // phpcs:ignore
            }

            return rtrim($path, $DS) . $DS;
        };

        // Retrieve incoming path containing files
        $pathIncoming = $createPathIfNotExists($path);

        // Retrieve processing path
        $pathProcessing = $createPathIfNotExists(dirname($pathIncoming) . $DS . self::IMPORT_PROCESSING_DIR);
        if (!empty(glob("$pathProcessing*.csv", GLOB_NOSORT))) {
            throw new \Exception(
                __('Another file is processing. Aborted.')->render()
            );
        }

        // Retrieve pending files and sort them by creation date (older first)
        $files = glob("$pathIncoming*.csv", GLOB_NOSORT);
        usort($files, function ($file1, $file2) {
            return filemtime($file1) - filemtime($file2);
        });

        if (!empty($files)) {
            // Retrieve archive path
            $pathArchiveParts = [dirname($pathIncoming), self::IMPORT_ARCHIVE_DIR, date('Y'), date('m'), date('d')];
            $pathArchive = $createPathIfNotExists(implode($DS, $pathArchiveParts));

            $moveFile = function ($srcFile, $destPath, $suffix = '') {
                $pathInfo = pathinfo($srcFile);
                $filename = $pathInfo['filename'] . $suffix . '.' . $pathInfo['extension'];
                $destFile = $destPath . $filename;

                return @rename($srcFile, $destFile) ? $destFile : false; // phpcs:ignore
            };

            foreach ($files as $i => $file) {
                if ($i >= $maxFiles) {
                    break;
                }

                // Try to move file to processing dir and run products import
                if ($file = $moveFile($file, $pathProcessing)) {
                    try {
                        $shop = $this->csvHelper->getShopFromFileName(basename($file));
                        $importId = $this->csvHelper->getImportIdFromFileName(basename($file));
                    } catch (\Exception $e) {
                        // Move file to archive dir and mark it as error
                        $moveFile($file, $pathArchive, '.ERROR');
                        continue;
                    }

                    $processFile = $this->processHelper->saveFile($file);

                    $process = $this->processFactory->create();
                    $process->setType(Process::TYPE_IMPORT)
                        ->setCode(\Mirakl\Mci\Helper\Product\Import::CODE)
                        ->setName('MCI products import from path')
                        ->setStatus(Process::STATUS_PENDING)
                        ->setHelper(\Mirakl\Mci\Helper\Product\Import::class)
                        ->setMethod('runFile')
                        ->setFile($processFile)
                        ->setParams([$shop->getId(), $importId]);

                    $process->output(__('Running file "%1"...', $file));

                    try {
                        // Run products import process
                        $process->run();

                        // Move file to archive dir and mark it as success
                        $moveFile($file, $pathArchive, '.SUCCESS');
                    } catch (\Exception $e) {
                        // Move file to archive dir and mark it as error
                        $moveFile($file, $pathArchive, '.ERROR');
                        $process->fail($e->getMessage());
                    }
                }
            }
        }

        return $this;
    }
}
