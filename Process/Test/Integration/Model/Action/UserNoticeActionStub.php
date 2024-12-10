<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action;

use Mirakl\Process\Model\Process;

class UserNoticeActionStub extends ActionStub
{
    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        trigger_error('This is a sample user notice');

        $process->output('Just triggered an invisible notice');

        return [];
    }
}