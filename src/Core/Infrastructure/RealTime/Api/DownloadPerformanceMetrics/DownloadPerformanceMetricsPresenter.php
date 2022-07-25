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

namespace Core\Infrastructure\RealTime\Api\DownloadPerformanceMetrics;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetricPresenterInterface;
use Core\Application\RealTime\UseCase\FindPerformanceMetrics\FindPerformanceMetricResponse;
use Core\Infrastructure\Common\Presenter\DownloadInterface;

class DownloadPerformanceMetricsPresenter extends AbstractPresenter implements FindPerformanceMetricPresenterInterface
{
    /**
     * {@inheritDoc}
     * @param FindPerformanceMetricResponse $presentedData
     */
    public function present(mixed $presentedData): void
    {
        parent::present($presentedData->performanceMetrics);
    }

    /**
     * Sets download file name in presenter
     *
     * @inheritDoc
     */
    public function setDownloadFileName(string $fileName): void
    {
        if ($this->presenterFormatter instanceof DownloadInterface) {
            $this->presenterFormatter->setDownloadFileName($fileName);
        }
    }
}
