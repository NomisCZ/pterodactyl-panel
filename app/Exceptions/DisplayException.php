<?php
/**
 * Pterodactyl - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace Pterodactyl\Exceptions;

use Log;
use Throwable;
use Prologue\Alerts\AlertsMessageBag;

class DisplayException extends PterodactylException
{
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';

    /**
     * @var string
     */
    protected $level;

    /**
     * Exception constructor.
     *
     * @param string         $message
     * @param Throwable|null $previous
     * @param string         $level
     * @param int            $code
     */
    public function __construct($message, Throwable $previous = null, $level = self::LEVEL_ERROR, $code = 0)
    {
        parent::__construct($message, $code, $previous);

        if (! is_null($previous)) {
            Log::{$level}($previous);
        }

        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getErrorLevel()
    {
        return $this->level;
    }

    /**
     * Render the exception to the user by adding a flashed message to the session
     * and then redirecting them back to the page that they came from. If the
     * request originated from an API hit, return the error in JSONAPI spec format.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json(Handler::convertToArray($this, [
                'detail' => $this->getMessage(),
            ]), 500);
        }

        app()->make(AlertsMessageBag::class)->danger($this->getMessage())->flash();

        return redirect()->back()->withInput();
    }
}
