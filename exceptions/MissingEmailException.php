<?php

namespace nkostadinov\user\exceptions;

/**
 * Intended to be used in cases where the user's email is required by the system but is not found.
 *
 * Mainly used in third-party authentications.
 *
 * @author Nikolay Traykov
 */
class MissingEmailException extends \yii\base\Exception
{
}