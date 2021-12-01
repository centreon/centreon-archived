import { CancelToken } from 'axios';

import { getData, putData } from '@centreon/ui';

import { SecurityPolicy, SecurityPolicyAPI } from '../models';

import { securityPolicyEndpoint } from './endpoints';
import { adaptSecurityPolicyToAPI } from './adapters';

export const getSecurityPolicy =
  (cancelToken: CancelToken) => (): Promise<SecurityPolicy> =>
    getData<SecurityPolicy>(cancelToken)(securityPolicyEndpoint);

export const putSecurityPolicy =
  (cancelToken: CancelToken) =>
  (securityPolicy: SecurityPolicy): Promise<unknown> =>
    putData<SecurityPolicyAPI, unknown>(cancelToken)({
      data: adaptSecurityPolicyToAPI(securityPolicy),
      endpoint: securityPolicyEndpoint,
    });
