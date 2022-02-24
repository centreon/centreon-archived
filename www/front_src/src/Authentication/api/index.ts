import { CancelToken } from 'axios';

import { getData, putData } from '@centreon/ui';

import {
  PasswordSecurityPolicy,
  PasswordSecurityPolicyFromAPI,
  PasswordSecurityPolicyToAPI,
} from '../Local/models';
import { Provider } from '../models';
import {
  OpenidConfiguration,
  OpenidConfigurationToAPI,
} from '../Openid/models';

import { authenticationProvidersEndpoint } from './endpoints';
import {
  adaptOpenidConfigurationToAPI,
  adaptPasswordSecurityPolicyToAPI,
} from './adapters';

export const getPasswordPasswordSecurityPolicy =
  (cancelToken: CancelToken) => (): Promise<PasswordSecurityPolicy> =>
    getData<PasswordSecurityPolicyFromAPI>(cancelToken)({
      endpoint: authenticationProvidersEndpoint(Provider.Local),
    }).then(
      (securityPolicy): PasswordSecurityPolicy =>
        securityPolicy.password_security_policy,
    );

export const putPasswordPasswordSecurityPolicy =
  (cancelToken: CancelToken) =>
  (securityPolicy: PasswordSecurityPolicy): Promise<unknown> =>
    putData<PasswordSecurityPolicyToAPI, unknown>(cancelToken)({
      data: adaptPasswordSecurityPolicyToAPI(securityPolicy),
      endpoint: authenticationProvidersEndpoint(Provider.Local),
    });

export const getOpenidConfiguration =
  (cancelToken: CancelToken) => (): Promise<OpenidConfiguration> =>
    getData<OpenidConfiguration>(cancelToken)({
      endpoint: authenticationProvidersEndpoint(Provider.Openid),
    });

export const putOpenidConfiguration =
  (cancelToken: CancelToken) =>
  (openidConfiguration: OpenidConfiguration): Promise<unknown> =>
    putData<OpenidConfigurationToAPI, unknown>(cancelToken)({
      data: adaptOpenidConfigurationToAPI(openidConfiguration),
      endpoint: authenticationProvidersEndpoint(Provider.Openid),
    });
