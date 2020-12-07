import * as React from 'react';

interface RefreshIntervalState {
  refreshInterval: number;
  setRefreshInterval: React.Dispatch<React.SetStateAction<number>>;
}

const useRefreshInterval = (): RefreshIntervalState => {
  const [refreshInterval, setRefreshInterval] = React.useState<number>(15);

  return { refreshInterval, setRefreshInterval };
};

export default useRefreshInterval;
