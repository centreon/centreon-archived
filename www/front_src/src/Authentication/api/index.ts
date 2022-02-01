import { CancelToken } from 'axios';

import { getData, putData } from '@centreon/ui';

import {
  SecurityPolicy,
  SecurityPolicyFromAPI,
  SecurityPolicyToAPI,
} from '../models';

import { securityPolicyEndpoint } from './endpoints';
import { adaptSecurityPolicyToAPI } from './adapters';

export const getSecurityPolicy =
  (cancelToken: CancelToken) => (): Promise<SecurityPolicy> =>
    getData<SecurityPolicyFromAPI>(cancelToken)({
      endpoint: securityPolicyEndpoint,
    }).then(
      (securityPolicy): SecurityPolicy =>
        securityPolicy.password_security_policy,
    );

export const putSecurityPolicy =
  (cancelToken: CancelToken) =>
  (securityPolicy: SecurityPolicy): Promise<unknown> =>
    putData<SecurityPolicyToAPI, unknown>(cancelToken)({
      data: adaptSecurityPolicyToAPI(securityPolicy),
      endpoint: securityPolicyEndpoint,
    });
