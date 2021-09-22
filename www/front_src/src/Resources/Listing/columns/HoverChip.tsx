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
  children: (props?) => JSX.Element;
  label: string;
  onClick?: () => void;
}

const HoverChip = ({ children, Chip, label, onClick }: Props): JSX.Element => {
  const [isChipHovered, setIsChipHovered] = React.useState<boolean>(false);

  const classes = useStyles();

  const openTooltip = (): void => setIsChipHovered(true);

  const closeTooltip = (): void => setIsChipHovered(false);

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
      open={isChipHovered}
      placement="left"
      title={children({ close: closeTooltip })}
      onClick={(e): void => {
        e.preventDefault();
        e.stopPropagation();

        onClick?.();
      }}
      onClose={closeTooltip}
      onOpen={openTooltip}
    >
      <span>
        <Chip />
      </span>
    </Tooltip>
  );
};

export default HoverChip;
