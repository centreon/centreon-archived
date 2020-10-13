<?php

function validateApiPort($port) {
    $port = filter_var($port, FILTER_VALIDATE_INT);

    if ($port === false || $port < 1 || $port > 65335) {
        return false;
    }

    return true;
}