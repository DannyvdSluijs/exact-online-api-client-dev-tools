<?php

declare(strict_types=1);

namespace DevTools\Exceptions;

class SerializationException extends \Exception implements ExceptionInterface
{
    public static function jsonEncodingFailed(): self
    {
        return new static('Something went wrong when encoding to json');
    }
}
