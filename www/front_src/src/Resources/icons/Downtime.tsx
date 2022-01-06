import React from 'react';

import { SvgIcon, SvgIconProps } from '@mui/material';

import { ReactComponent as IconDowntime } from './downtime.icon.svg';

const Downtime = (props: SvgIconProps): JSX.Element => (
  <SvgIcon component={IconDowntime} {...props} />
);

export default Downtime;
