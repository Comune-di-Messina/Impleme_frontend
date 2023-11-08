<?php
namespace Drupal\wso2_with_jwt;

abstract class AuthType {
    const NONE = -1;
    const BOTH = 0;
    const ACCESS_TOKEN_ONLY = 1;
    const JWT_ONLY = 2;
}