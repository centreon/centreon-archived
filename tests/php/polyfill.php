<?php
/**
 * Copyright 2019 Centreon
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
 */


// add gnupg class polyfill
if (!class_exists('gnupg')) {

    class gnupg
    {

        const SIG_MODE_CLEAR = null;

        public function import($keydata = null): array
        {
            return [];
        }

        public function setsignmode(int $signmode = null): bool
        {
            return true;
        }

        public function verify($licensedata, $status, &$plaintext)
        {
            return true;
        }
    }

}
