<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ValidacionException extends Exception
{
    private $errors;
    
    public function __construct(string $message = "", $errors = null, int $code = 0, Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors()
    {
        return $this->errors;
    }

}
