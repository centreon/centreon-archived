<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Application\Normalizer;

use Centreon\Domain\Configuration\Icon\Icon as ConfigurationIcon;
use Centreon\Domain\Monitoring\Icon as MonitoringIcon;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize icon url to build full url
 */
class IconUrlNormalizer implements NormalizerInterface
{
    private const IMG_DIR = '/img/media';

    /**
     * Concat base url with icon path to get full url
     * {@inheritDoc}
     */
    public function normalize($icon, $format = null, array $context = [])
    {
        if (isset($_SERVER['REQUEST_URI']) && preg_match('/^(.+)\/api\/.+/', $_SERVER['REQUEST_URI'], $matches)) {
            $icon->setUrl($matches[1] . self::IMG_DIR . '/' . $icon->getUrl());
        }

        return $icon;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ConfigurationIcon || $data instanceof MonitoringIcon;
    }
}
