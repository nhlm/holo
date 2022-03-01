<?php declare(strict_types=1);
/**
 * Holo Project
 * 
 * (c) 2022 Matthias "nihylum" Kaschubowski
 * 
 * @package holo
 */
namespace Holo\Exception;

use ErrorException;
use const E_USER_ERROR;

class HttpException extends ErrorException {

    protected int $statusCode = 500;

    public function __construct(int $statusCode, string $message, int $code = 0)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, E_USER_ERROR);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

}