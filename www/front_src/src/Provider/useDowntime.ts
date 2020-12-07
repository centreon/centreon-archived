import * as React from 'react';

import { Downtime } from './models';

interface DowntimeState {
  downtime: Downtime;
  setDowntime: React.Dispatch<React.SetStateAction<Downtime>>;
}

const useDowntime = (): DowntimeState => {
  const [downtime, setDowntime] = React.useState<Downtime>({
    default_duration: 7200,
  });

  return { downtime, setDowntime };
};

export default useDowntime;
