import { JsonDecoder } from 'ts.data.json';

import { PlatformVersions, Redirect } from '../models';

export const redirectDecoder = JsonDecoder.object<Redirect>(
  {
    redirectUri: JsonDecoder.string,
  },
  'Redirect Decoder',
  {
    redirectUri: 'redirect_uri',
  },
);

export const platformVersionsDecoder = JsonDecoder.object<PlatformVersions>(
  {
    web: JsonDecoder.object<PlatformVersions['web']>(
      {
        version: JsonDecoder.string,
      },
      'Web versions',
    ),
  },
  'Platform versions Decoder',
);
