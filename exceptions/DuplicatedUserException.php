<?php

namespace nkostadinov\user\exceptions;

/**
 * Intended to be used in cases where we want to register a user that already exists in the platform.
 *
 * Mainly used in third-party authentications.
 *
 * @author Nikolay Traykov
 */
class DuplicatedUserException extends \yii\base\Exception
{
}