<?php

namespace App\Lib\Tools;

interface ToolInterface
{
    public function execute(...$args);
    public function getName(): string;
    public function getDescription(): string;
}
