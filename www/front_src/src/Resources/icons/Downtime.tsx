import React from 'react';

import { SvgIcon, SvgIconProps } from '@material-ui/core';

import { ReactComponent as IconDowntime } from './downtime.icon.svg';

const Downtime = (props: SvgIconProps): JSX.Element => (
  <SvgIcon component={IconDowntime} {...props} />
);

export default Downtime;
