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

use Centreon\Domain\Log\LoggerTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvPresenter implements PresenterFormatterInterface
{
    use LoggerTrait;

    /**
     * @var mixed $data
     */
    private mixed $data = null;

    public function setResponseHeaders(array $responseHeaders): void
    {
        // TODO: Implement setResponseHeaders() method.
    }

    public function getResponseHeaders(): array
    {
        // TODO: Implement getResponseHeaders() method.
    }

    public function present(mixed $data): void
    {
        // TODO: Implement present() method.
    }

    public function show(): Response
    {
        $response = new StreamedResponse();
        $response->setCallback(function () use($dataBin, $metrics) {
            $handle = fopen('php://output', 'r+');
            if ($handle === false) {
                throw new \RuntimeException('Unable to generate file');
            }

            foreach ($dataBin as $data) {
                fputcsv($handle, $data, ';');
            }

            fclose($handle);
        });
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}