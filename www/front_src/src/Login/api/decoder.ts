import { JsonDecoder } from 'ts.data.json';

import { Redirect } from '../models';

export const redirectDecoder = JsonDecoder.object<Redirect>(
  {
    redirectUri: JsonDecoder.string,
  },
  'Redirect Decoder',
  {
    redirectUri: 'redirect_uri',
  },
);
