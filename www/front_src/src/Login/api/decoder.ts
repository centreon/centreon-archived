import { JsonDecoder } from 'ts.data.json';

import { PlatformVersions, ProviderConfiguration, Redirect } from '../models';

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

const providerConfigurationDecoder = JsonDecoder.object<ProviderConfiguration>(
  {
    authenticationUri: JsonDecoder.string,
    id: JsonDecoder.number,
    isActive: JsonDecoder.boolean,
    name: JsonDecoder.string,
  },
  'Provider Condifugration',
  {
    authenticationUri: 'authentication_uri',
    isActive: 'is_active',
  },
);

export const providersConfigurationDecoder = JsonDecoder.array(
  providerConfigurationDecoder,
  'Providers Configuration List',
);
