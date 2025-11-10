<?php

namespace NckRtl\HttpManager\Enums;

enum AuthenticationType: string
{
    case Bearer = 'Bearer';
    case ApiKey = 'ApiKey';
    case Basic = 'Basic';
    case Custom = 'Custom';
}
