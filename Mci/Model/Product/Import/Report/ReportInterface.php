<?php

declare(strict_types=1);

namespace Mirakl\Mci\Model\Product\Import\Report;

interface ReportInterface
{
    /**
     * @return mixed
     */
    public function getContents();

    /**
     * @param array $data
     * @return mixed
     */
    public function write(array $data);

    /**
     * @return void
     */
    public function clear();
}
