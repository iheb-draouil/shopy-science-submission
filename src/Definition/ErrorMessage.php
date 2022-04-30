<?php

namespace App\Definition;

class ErrorMessage
{
    const INVALID_USERNAME_OR_PASSWORD = 'invalid-username-or-password';
    const INVALID_REQUEST_FORMAT = 'invalid-REQUEST-password';
    const ABSENT_REFRESH_TOKEN = 'absent-refresh-token';
    const LOGIN_REQUIRED = 'login-required';

    const ABSENT_AUTHORIZATION_HEADER = 'absent-authorization-header';
    const ABSENT_AUTHORIZATION_COOKIE = 'absent-authorization-cookie';

    const EXPIRED_REFRESH_TOKEN = 'expired-refresh-token';
    const INVALID_REFRESH_TOKEN = 'invalid-refresh-token';

    const EXPIRED_ACCESS_TOKEN = 'expired-access-token';
    const INVALID_ACCESS_TOKEN = 'invalid-access-token';

    const EXPIRED_JWT_TOKEN = 'expired-jwt-token';
    const INVALID_JWT_TOKEN = 'invalid-jwt-token';
    const ABSENT_COOKIE = 'absent-cookie';

    const UNEXPECTED_SERVER_ERROR = 'unexpected-server-error';
    const DESERIALIZATION_FAILURE = 'deserialization-failure';
    const NULL_ARGUMENT = 'null-argument';

    const VALIDATION_FAILURE = 'validation-failure';
    const DUPLICATE_RECORD = 'duplicate-record';
    const PARSING_FAILURE = 'parsing-failure';
    const EXPIRED_TOKEN = 'expired-token';
}