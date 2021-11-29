import { CancelToken } from 'axios';

import { getData } from '@centreon/ui';

import { SecurityPolicy } from '../models';

import { securityPolicyEndpoint } from './endpoints';

export const getSecurityPolicy =
  (cancelToken: CancelToken) => (): Promise<SecurityPolicy> =>
    getData<SecurityPolicy>(cancelToken)(securityPolicyEndpoint);
