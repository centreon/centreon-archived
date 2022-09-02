import { useState, useEffect } from 'react';

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
  const [isCeipEnabled, setIsCeipEnabled] = useState(false);
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

  const goOnline = (): void => {
    sendCeipInfo(true);
  };

  const goOffline = (): void => {
    sendCeipInfo(false);
  };

  useEffect(() => {
    if (isNil(window.fetch)) {
      return;
    }

    activateCeip();
  }, []);

  useEffect(() => {
    window.addEventListener('online', goOnline);
    window.addEventListener('offline', goOffline);

    sendCeipInfo(window.navigator.onLine);

    return (): void => {
      window.removeEventListener('online', goOnline);
      window.removeEventListener('offline', goOffline);
    };
  }, [isCeipEnabled]);
};

export default usePendo;
