import * as React from 'react';

import { Tooltip, makeStyles } from '@material-ui/core';

const useStyles = makeStyles(() => ({
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
  Chip: () => JSX.Element;
  label: string;
  onClick?: () => void;
}

const HoverChip = ({ children, Chip, label, onClick }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip
      placement="left"
      title={children}
      classes={{ tooltip: classes.tooltip }}
      aria-label={label}
      enterDelay={0}
      interactive
      PopperProps={{
        onClick: (e): void => {
          e.preventDefault();
          e.stopPropagation();
        },
      }}
      onClick={(e): void => {
        e.preventDefault();
        e.stopPropagation();

        onClick?.();
      }}
    >
      <div>
        <Chip />
      </div>
    </Tooltip>
  );
};

export default HoverChip;
