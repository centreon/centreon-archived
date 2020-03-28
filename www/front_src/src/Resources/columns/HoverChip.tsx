import * as React from 'react';

import { Tooltip, IconButton, makeStyles } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  chip: {
    width: theme.spacing(4),
    height: theme.spacing(4),
  },
  tooltip: {
    maxWidth: 'none',
    backgroundColor: 'transparent',
  },
  iconButton: {
    padding: 0,
  },
}));

interface Props {
  children: React.ReactNode;
  ariaLabel: string;
  Icon: React.SFC;
  className?: string;
}

const HoverChip = ({
  className,
  children,
  ariaLabel,
  Icon,
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip
      placement="left"
      title={children}
      classes={{ tooltip: classes.tooltip }}
      enterDelay={0}
      interactive
    >
      <IconButton
        aria-label={ariaLabel}
        classes={{ root: classes.iconButton }}
        className={className}
      >
        <Icon />
      </IconButton>
    </Tooltip>
  );
};

export default HoverChip;
