import { JsonDecoder } from 'ts.data.json';

import { ProviderConfiguration, Redirect } from '../models';

export const redirectDecoder = JsonDecoder.object<Redirect>(
  {
    passwordIsExpired: JsonDecoder.optional(JsonDecoder.boolean),
    redirectUri: JsonDecoder.string,
  },
  'Redirect Decoder',
  {
    passwordIsExpired: 'password_is_expired',
    redirectUri: 'redirect_uri',
  },
);

const providerConfigurationDecoder = JsonDecoder.object<ProviderConfiguration>(
  {
    authenticationUri: JsonDecoder.string,
    id: JsonDecoder.number,
    isActive: JsonDecoder.boolean,
    isForced: JsonDecoder.optional(JsonDecoder.boolean),
    name: JsonDecoder.string,
  },
  'Provider Condifugration',
  {
    authenticationUri: 'authentication_uri',
    isActive: 'is_active',
    isForced: 'is_forced',
  },
);

export const providersConfigurationDecoder = JsonDecoder.array(
  providerConfigurationDecoder,
  'Providers Configuration List',
);
