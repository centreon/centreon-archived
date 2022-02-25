import { JsonDecoder } from 'ts.data.json';

import { User } from '@centreon/ui-context';

import { PlatformInstallationStatus } from './models';

export const userDecoder = JsonDecoder.object<User>(
  {
    alias: JsonDecoder.string,
    default_page: JsonDecoder.optional(
      JsonDecoder.nullable(JsonDecoder.string),
    ),
    isExportButtonEnabled: JsonDecoder.boolean,
    locale: JsonDecoder.string,
    name: JsonDecoder.string,
    timezone: JsonDecoder.string,
    use_deprecated_pages: JsonDecoder.boolean,
  },
  'User parameters',
  {
    isExportButtonEnabled: 'is_export_button_enabled',
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
