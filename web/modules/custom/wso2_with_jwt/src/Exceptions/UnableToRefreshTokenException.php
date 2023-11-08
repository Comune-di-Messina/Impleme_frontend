<?php
namespace Drupal\wso2_with_jwt\Exceptions;

class UnableToRefreshTokenException extends \Exception {

    public function __construct(String $message = "", int $code = 0, \Throwable $previous = null) {
        parent::__construct(sprintf("%s: %s.", "Unable to refresh token", $message), $code, $previous);
    }
}