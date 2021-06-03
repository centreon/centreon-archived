import * as React from 'react';

import { Tooltip, makeStyles } from '@material-ui/core';

const useStyles = makeStyles(() => ({
  iconButton: {
    padding: 0,
  },
  tooltip: {
    backgroundColor: 'transparent',
    maxWidth: 'none',
  },
}));

interface Props {
  Chip: () => JSX.Element;
  children: React.ReactElement;
  label: string;
  onClick?: () => void;
}

const HoverChip = ({ children, Chip, label, onClick }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip
      interactive
      PopperProps={{
        onClick: (e): void => {
          e.preventDefault();
          e.stopPropagation();
        },
      }}
      aria-label={label}
      classes={{ tooltip: classes.tooltip }}
      enterDelay={200}
      enterNextDelay={200}
      leaveDelay={0}
      placement="left"
      title={children}
      onClick={(e): void => {
        e.preventDefault();
        e.stopPropagation();

        onClick?.();
      }}
    >
      <span>
        <Chip />
      </span>
    </Tooltip>
  );
};

export default HoverChip;
