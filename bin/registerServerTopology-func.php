<?php

/**
 * Ask question. The echo of keyboard can be disabled
*
* @param string $question
* @param boolean $hidden
* @return string
*/
function askQuestion(string $question, $hidden = false): string
{
    if ($hidden) {
        system("stty -echo");
    }
    printf("%s", $question);
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    if ($hidden) {
        system("stty echo");
    }
    printf("\n");
    return $response;
}

/**
 * Format the response for API Request
 *
 * @param integer $code
 * @param string $message
 * @param string $type
 * @return string
 */
function formatResponseMessage(int $code, string $message, string $type = 'success'): string
{
    switch ($type) {
        case 'error':
            $responseMessage = 'error code: ' . $code . PHP_EOL .
            'error message: ' . $message . PHP_EOL;
            break;
        case 'success':
        default:
            $responseMessage = 'code: ' . $code . PHP_EOL .
            'message: ' . $message . PHP_EOL;
            break;
    }

    return sprintf('%s', $responseMessage);
}
