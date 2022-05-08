import { useAtom } from 'jotai';
import { isNil, keys } from 'ramda';

import { getData, useRequest } from '@centreon/ui';

import { platformVersionsEndpoint } from '../api/endpoint';
import { PlatformVersions } from '../api/models';

import { platformVersionsAtom } from './atoms/platformVersionsAtom';

interface UsePlatformVersionsState {
  getModules: () => Array<string> | null;
  getPlatformVersions: () => void;
}

const usePlatformVersions = (): UsePlatformVersionsState => {
  const { sendRequest: sendPlatformVersions } = useRequest<PlatformVersions>({
    request: getData,
  });

  const [platformVersions, setPlatformVersions] = useAtom(platformVersionsAtom);

  const getPlatformVersions = (): void => {
    sendPlatformVersions({ endpoint: platformVersionsEndpoint }).then(
      setPlatformVersions,
    );
  };

  const getModules = (): Array<string> | null => {
    if (isNil(platformVersions)) {
      return null;
    }

    return keys(platformVersions?.modules) as Array<string>;
  };

  return {
    getModules,
    getPlatformVersions,
  };
};

export default usePlatformVersions;
