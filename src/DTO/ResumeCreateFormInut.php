<?php

declare(strict_types=1);

namespace App\DTO;

class ResumeCreateFormInut
{
    public function __construct(
      public string $resumeName = '',
      public string $file = '',
    )
    {
      
    }
}
