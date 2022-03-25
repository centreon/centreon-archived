import * as React from 'react';

import { gte, isNil, not } from 'ramda';
import { atomWithStorage } from 'jotai/utils';
import { useAtom } from 'jotai';

import { getData, useRequest } from '@centreon/ui';

import initPendo from './initPendo';
import { CeipData } from './models';

const oneDayInMs = 24 * 60 * 60 * 1000;

const centreonPlatformDataAtom = atomWithStorage<CeipData | null>(
  'centreonPlatformData',
  null,
);

const usePendo = (): void => {
  const [isCeipEnabled, setIsCeipEnabled] = React.useState(false);
  const { sendRequest } = useRequest<CeipData>({
    request: getData,
  });

  const [centreonPlatformData, setCentreonPlatformData] = useAtom(
    centreonPlatformDataAtom,
  );

  const sendCeipInfo = (isOnline: boolean): void => {
    if (not(isCeipEnabled) || not(isOnline)) {
      return;
    }

    sendRequest({
      endpoint: './api/internal.php?object=centreon_ceip&action=ceipInfo',
    }).then((data) => {
      if (not(data.ceip)) {
        setCentreonPlatformData({ ceip: false });

        return;
      }

      initPendo(data);

      const platformData = {
        account: data.account,
        cacheGenerationDate: Date.now(),
        ceip: true,
        excludeAllText: data.excludeAllText,
        visitor: data.visitor,
      };
      setCentreonPlatformData(platformData);
    });
  };

  const activateCeip = (): void => {
    if (isNil(centreonPlatformData)) {
      setIsCeipEnabled(true);

      return;
    }

    try {
      const isCacheOutdated = gte(
        (centreonPlatformData?.cacheGenerationDate || 0) + oneDayInMs,
        Date.now(),
      );

      if (not(isCacheOutdated)) {
        setIsCeipEnabled(true);

        return;
      }

      if (not(centreonPlatformData.ceip)) {
        return;
      }

      initPendo(centreonPlatformData);
    } catch (e) {
      setIsCeipEnabled(true);
    }
  };

  const online = (): void => {
    sendCeipInfo(true);
  };

  const offline = (): void => {
    sendCeipInfo(false);
  };

  React.useEffect(() => {
    if (isNil(window.fetch)) {
      return;
    }

    activateCeip();
  }, []);

  React.useEffect(() => {
    window.addEventListener('online', online);
    window.addEventListener('offline', offline);

    sendCeipInfo(window.navigator.onLine);

    return (): void => {
      window.removeEventListener('online', online);
      window.removeEventListener('offline', offline);
    };
  }, [isCeipEnabled]);
};

export default usePendo;
