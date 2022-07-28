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

namespace Core\Infrastructure\Common\Presenter;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvPresenter extends AbstractPresenter implements PresenterFormatterInterface
{
    private mixed $data = null;

    /**
     * @inheritDoc
     */
    public function present(mixed $data): void
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function show(): Response
    {
        $response = new StreamedResponse(null, Response::HTTP_OK, $this->responseHeaders);
        $response->setCallback(function () {
            $handle = fopen('php://output', 'r+');
            if ($handle === false) {
                throw new \RuntimeException('Unable to open the output buffer');
            }
            $lineHeadersCreated = false;
            foreach ($this->data as $data) {
                if (! $lineHeadersCreated) {
                    $columnNames = array_keys($data);
                    fputcsv($handle, $columnNames, ';');
                    $lineHeadersCreated = true;
                }
                $columnValues = array_values($data);
                fputcsv($handle, $columnValues, ';');
            }

            fclose($handle);
        });

        return $response;
    }
}
