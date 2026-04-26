<?php

namespace App\Ai\Tools;

use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

abstract class SparepartsAction implements Tool
{
    protected ?int $userId = null;

    public function forUser(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }
}
