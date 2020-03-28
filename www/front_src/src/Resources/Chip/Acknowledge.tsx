import * as React from 'react';

import IconAcknowledge from '@material-ui/icons/Person';

import Chip from '.';

import { acknwoledgeColor } from '../colors';

const AcknwoledgeChip = (): JSX.Element => (
  <Chip icon={<IconAcknowledge />} color={acknwoledgeColor} />
);

export default AcknwoledgeChip;
