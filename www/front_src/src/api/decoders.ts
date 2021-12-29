import { JsonDecoder } from 'ts.data.json';

import { User } from '@centreon/ui-context';

import { WebVersions } from './models';

export const userDecoder = JsonDecoder.object<User>(
  {
    alias: JsonDecoder.string,
    default_page: JsonDecoder.string,
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

export const webVersionsDecoder = JsonDecoder.object<WebVersions>(
  {
    availableVersion: JsonDecoder.nullable(JsonDecoder.string),
    isInstalled: JsonDecoder.boolean,
  },
  'Web versions',
  {
    availableVersion: 'available_version',
    isInstalled: 'is_installed',
  },
);
