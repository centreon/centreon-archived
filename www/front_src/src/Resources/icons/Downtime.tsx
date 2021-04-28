import React from 'react';

import { Icon, makeStyles, SvgIcon, SvgIconProps } from '@material-ui/core';

import DowntimeIcon from './downtime.icon.svg';

const useStyles = makeStyles({
  iconRoot: {
    textAlign: 'center',
  },
  imageIcon: {
    height: '100%',
  },
});

const Downtime = (props: SvgIconProps): JSX.Element => {
  const classes = useStyles();

  return (
    <Icon classes={{ root: classes.iconRoot }}>
      <img alt="Downtime" className={classes.imageIcon} src={DowntimeIcon} />
    </Icon>
  );
};
export default Downtime;
