import * as React from 'react';

import IconAcknwoledge from '@material-ui/icons/Person';

import Chip from '.';

import { acknwoledgeColor } from '../colors';

const AcknwoledgeChip = (): JSX.Element => (
  <Chip icon={<IconAcknwoledge />} color={acknwoledgeColor} />
);

export default AcknwoledgeChip;
