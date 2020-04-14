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
  children: React.ReactElement;
  Chip: () => JSX.Element;
  label: string;
  onClick?: () => void;
  onHover?: () => void;
}

const HoverChip = ({
  children,
  Chip,
  label,
  onClick,
  onHover,
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip
      placement="left"
      title={children}
      classes={{ tooltip: classes.tooltip }}
      aria-label={label}
      enterDelay={200}
      enterNextDelay={200}
      leaveDelay={0}
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
      onOpen={onHover}
    >
      <span>
        <Chip />
      </span>
    </Tooltip>
  );
};

export default HoverChip;
