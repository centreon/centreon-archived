import { JsonDecoder } from 'ts.data.json';

import { PlatformVersions, Redirect } from '../models';

export const redirectDecoder = JsonDecoder.object<Redirect>(
  {
    passwordIsExpired: JsonDecoder.boolean,
    passwordRemainingTime: JsonDecoder.number,
    redirectUri: JsonDecoder.string,
  },
  'Redirect Decoder',
  {
    passwordIsExpired: 'password_is_expired',
    passwordRemainingTime: 'password_remaining_time',
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
