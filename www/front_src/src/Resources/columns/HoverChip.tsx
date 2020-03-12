import * as React from 'react';

import { Tooltip, Avatar, makeStyles } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  chip: {
    width: theme.spacing(4),
    height: theme.spacing(4),
  },
  tooltip: {
    maxWidth: 'none',
    backgroundColor: 'transparent',
  },
}));

interface Props {
  children: React.ReactNode;
  ariaLabel: string;
  Icon: React.SFC;
  className?: string;
}

const HoverChip = ({
  children,
  ariaLabel,
  Icon,
  className,
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip
      placement="left"
      title={children}
      classes={{ tooltip: classes.tooltip }}
      enterDelay={0}
    >
      <Avatar aria-label={ariaLabel} className={`${classes.chip} ${className}`}>
        <Icon />
      </Avatar>
    </Tooltip>
  );
};

export default HoverChip;
