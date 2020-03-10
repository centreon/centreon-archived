import * as React from 'react';

import { Tooltip, Avatar, makeStyles } from '@material-ui/core';

const useStyles = makeStyles(() => ({
  tooltip: {
    maxWidth: 'none',
    backgroundColor: 'transparent',
  },
}));

interface Props {
  children: React.ReactNode;
  ariaLabel: string;
  Icon: React.SFC;
}

const HoverChip = ({ children, ariaLabel, Icon }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip
      placement="left"
      title={children}
      classes={{ tooltip: classes.tooltip }}
      enterDelay={0}
    >
      <Avatar aria-label={ariaLabel}>
        <Icon />
      </Avatar>
    </Tooltip>
  );
};

export default HoverChip;
