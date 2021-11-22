<?php
/**
 * Anubis Importer Errors Trait
 * Print Log Errors
 */
trait Errors {
    function log_errors() {
        $log = date("F j, Y, g:i a", current_time( 'timestamp', 0 )).PHP_EOL;
        foreach ($this->count_error as $key => $value) {
            $log .= $key . ": " . $value . PHP_EOL;
        }
        $log .= "---------------------------------------------".PHP_EOL;
        file_put_contents(ANUBIS_IMPORTER_LOG_ERROR_FILE, $log, FILE_APPEND);
    }
}