import React from 'react';

import { SvgIcon } from '@material-ui/core';

import { ReactComponent as IconDowntime } from './downtime.icon.svg';

const Downtime = (): JSX.Element => <SvgIcon component={IconDowntime} />;

export default Downtime;
