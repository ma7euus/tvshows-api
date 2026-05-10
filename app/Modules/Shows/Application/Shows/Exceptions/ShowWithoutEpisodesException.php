<?php

namespace App\Modules\Shows\Application\Shows\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class ShowWithoutEpisodesException extends UnprocessableEntityHttpException
{
    public function __construct()
    {
        parent::__construct('No episodes available for the selected show.');
    }
}
