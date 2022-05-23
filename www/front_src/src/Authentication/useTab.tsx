import { useEffect } from 'react';

import { useAtom, useAtomValue } from 'jotai';
import { equals, not } from 'ramda';

import { appliedTabAtom, tabAtom } from './tabAtoms';

const useTab = (isConfigurationEmpty: boolean): void => {
  const [appliedTab, setAppliedTab] = useAtom(appliedTabAtom);
  const tab = useAtomValue(tabAtom);

  useEffect(() => {
    if (not(isConfigurationEmpty) || equals(tab, appliedTab)) {
      return;
    }

    setAppliedTab(tab);
  }, [tab]);
};

export default useTab;
