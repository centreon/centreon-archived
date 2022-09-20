<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

class HtmlAnalyzer
{
    private int $index;
    private mixed $stringToSanitize;
    private int $deepTag = 0;

    /**
     * Sanitize and remove html tags
     *
     * @param mixed $stringToSanitize
     * @return string
     */
    public static function sanitizeAndRemoveTags($stringToSanitize): string
    {
        $stringToSanitize = (string) $stringToSanitize;

        $html = new self($stringToSanitize);

        $newString = $html->removeHtmlTag();

        return str_replace(["'", '"'], ['&#39;', '&#34;'], $newString);
    }

    /**
     * @param string $stringToSanitize
     */
    public function __construct(string $stringToSanitize)
    {
        $this->index = -1;
        $this->stringToSanitize = $stringToSanitize;
        $this->deepTag = 0;
    }

    /**
     * Remove html tag
     *
     * @return string
     */
    public function removeHtmlTag(): string
    {
        $newString = '';
        while (($token = $this->getNextToken()) !== null) {
            if ($token === '<') {
                $this->deepTag++;
            } elseif ($token === '>') {
                if ($this->deepTag > 0) {
                    $this->deepTag--;
                } else {
                    $newString .= $token;
                }
            } elseif ($this->deepTag === 0) {
                $newString .= $token;
            }
        }

        return $newString;
    }

    /**
     * Get next token
     *
     * @return string|null
     */
    private function getNextToken(): ?string
    {
        $this->index++;
        if (mb_strlen($this->stringToSanitize) > $this->index) {
            return mb_substr($this->stringToSanitize, $this->index, 1);
        }

        return null;
    }
}
