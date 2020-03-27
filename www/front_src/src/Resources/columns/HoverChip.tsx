import * as React from 'react';

import { Tooltip, makeStyles } from '@material-ui/core';

const useStyles = makeStyles(() => ({
  tooltip: {
    maxWidth: 'none',
    backgroundColor: 'transparent',
  },
}));

interface Props {
  children: React.ReactNode;
  Chip: () => JSX.Element;
  label: string;
}

const HoverChip = ({ children, Chip, label }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip
      placement="left"
      title={children}
      classes={{ tooltip: classes.tooltip }}
      aria-label={label}
      enterDelay={0}
      onClick={(e): void => {
        e.preventDefault();
        e.stopPropagation();
      }}
    >
      <div>
        <Chip />
      </div>
    </Tooltip>
  );
};

export default HoverChip;
