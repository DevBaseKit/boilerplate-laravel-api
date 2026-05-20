<?php

namespace App\Constants;

use Symfony\Component\HttpFoundation\Response;

class ApiStatusCode
{
    public const OK = Response::HTTP_OK;
    public const CREATED = Response::HTTP_CREATED;
    public const BAD_REQUEST = Response::HTTP_BAD_REQUEST;
    public const UNAUTHORIZED = Response::HTTP_UNAUTHORIZED;
    public const FORBIDDEN = Response::HTTP_FORBIDDEN;
    public const NOT_FOUND = Response::HTTP_NOT_FOUND;
    public const UNPROCESSABLE_ENTITY = Response::HTTP_UNPROCESSABLE_ENTITY;
    public const TOO_MANY_REQUESTS = Response::HTTP_TOO_MANY_REQUESTS;
    public const INTERNAL_SERVER_ERROR = Response::HTTP_INTERNAL_SERVER_ERROR;
}
