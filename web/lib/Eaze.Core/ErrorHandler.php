<?php

class PhpException extends ErrorException {}
class UndefinedVariableException extends PhpException {}
class UndefinedOffsetException extends PhpException {}
class UndefinedPropertyException extends PhpException {}
class IncludeException extends PhpException {}

/**
 * Обработка php-ошибок и конвертация их в исключения
 *
 * @author akhmedyanov
 */
final class ErrorHandler {
    /**
     * @var int
     */
    const DEFAULT_SEVERITY = 1;

    /**
     * @param  int    $no
     * @param  string $str
     * @param  string $file
     * @param  int    $line
     * @return void
     * @throws PhpException
     * @throws UndefinedOffsetException
     * @throws UndefinedPropertyException
     * @throws UndefinedVariableException
     * @throws IncludeException
     * @link   http://php.net/manual/en/language.operators.errorcontrol.php
     * @link   http://www.php.net/manual/en/function.error-reporting.php
     * @link   http://www.php.net/manual/en/errorfunc.constants.php
     */
    public function handleError($no, $str, $file, $line) {
        if (!error_reporting()) {
            // If you have set a custom error handler function with set_error_handler() then it will still get called,
            // but this custom error handler can (and should) call error_reporting() which will return 0
            // when the call that triggered the error was preceded by an @.
            // http://php.net/manual/en/language.operators.errorcontrol.php
            return;
        }

        if ($no == E_NOTICE) {
            if (self::isStartsWith($str, 'Undefined variable: ')) {
                $Ex = new UndefinedVariableException($str, $no, self::DEFAULT_SEVERITY, $file, $line);
            } elseif (self::isStartsWith($str, 'Undefined offset: ') ||
                self::isStartsWith($str, 'Undefined index: ')) {
                $Ex = new UndefinedOffsetException($str, $no, self::DEFAULT_SEVERITY, $file, $line);
            } elseif (self::isStartsWith($str, 'Undefined property: ')) {
                $Ex = new UndefinedPropertyException($str, $no, self::DEFAULT_SEVERITY, $file, $line);
            }
        } elseif ($no == E_WARNING) {
            if (self::isStartsWith($str, 'include(')) {
                $Ex = new IncludeException($str, $no, self::DEFAULT_SEVERITY, $file, $line);
            }
        }

        $Ex = new PhpException($str, $no, self::DEFAULT_SEVERITY, $file, $line);

        error_log($Ex);
    }

    private static function isStartsWith($str, $prefix) {
        return substr($str, 0, strlen($prefix)) == $prefix;
    }

    /**
     * @return void
     */
    public function register() {
        set_error_handler(array($this, 'handleError'));
        register_shutdown_function(array($this, 'handleFatalError'));
    }

    /**
     * Обработчик фатальных ошибок
     */
    public function handleFatalError() {
        $error = error_get_last();
        if (
            !is_array($error) ||
            (
                $error['type'] != E_ERROR &&
                $error['type'] != E_PARSE &&
                $error['type'] != E_CORE_ERROR &&
                $error['type'] != E_COMPILE_ERROR
            )
        ) {
            return;
        }

        $typeNames = array(
            E_ERROR => 'E_ERROR',
            E_PARSE => 'E_PARSE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        );

        $msg = '(' . $typeNames[$error['type']] . '): ' . $error['message'];
        $Ex = new ErrorException($msg, $error['type'], 1, $error['file'], $error['line']);

        error_log("Fatal error on page : " . Page::$Uri);
        error_log($Ex);
    }
}