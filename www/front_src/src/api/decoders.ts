import { JsonDecoder } from 'ts.data.json';

import { User } from '@centreon/ui-context';

import { PlatformInstallationStatus } from './models';

export const userDecoder = JsonDecoder.object<User>(
  {
    alias: JsonDecoder.string,
    defaultPage: JsonDecoder.optional(JsonDecoder.nullable(JsonDecoder.string)),
    isExportButtonEnabled: JsonDecoder.boolean,
    locale: JsonDecoder.string,
    name: JsonDecoder.string,
    passwordRemainingTime: JsonDecoder.nullable(JsonDecoder.number),
    timezone: JsonDecoder.string,
    useDeprecatedPages: JsonDecoder.boolean,
  },
  'User parameters',
  {
    defaultPage: 'default_page',
    isExportButtonEnabled: 'is_export_button_enabled',
    passwordRemainingTime: 'password_remaining_time',
    useDeprecatedPages: 'use_deprecated_pages',
  },
);

export const webVersionsDecoder =
  JsonDecoder.object<PlatformInstallationStatus>(
    {
      hasUpgradeAvailable: JsonDecoder.boolean,
      isInstalled: JsonDecoder.boolean,
    },
    'Web versions',
    {
      hasUpgradeAvailable: 'has_upgrade_available',
      isInstalled: 'is_installed',
    },
  );
