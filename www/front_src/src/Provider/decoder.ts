import { JsonDecoder } from 'ts.data.json';

import { User } from '@centreon/ui-context';

export const userDecoder = JsonDecoder.object<User>(
  {
    alias: JsonDecoder.string,
    isExportButtonEnabled: JsonDecoder.boolean,
    locale: JsonDecoder.string,
    name: JsonDecoder.string,
    timezone: JsonDecoder.string,
  },
  'User parameters',
  {
    isExportButtonEnabled: 'is_export_button_enabled',
  },
);
