<?php

declare(strict_types=1);

namespace App\DTO;

class WorksCreateFromInput
{
    public function __construct(
      public string $worksName = '',
      public string $url = '',
    )
    {
      
    }
}
