import * as React from 'react';

import Chip from '.';
import IconDowntime from '../icons/Downtime';
import { downtimeColor } from '../colors';

const DowntimeChip = (): JSX.Element => (
  <Chip icon={<IconDowntime />} color={downtimeColor} />
);

export default DowntimeChip;
