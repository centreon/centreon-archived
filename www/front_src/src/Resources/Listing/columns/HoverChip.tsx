import * as React from 'react';

import { Tooltip } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

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
  const classes = useStyles();

  const [isChipHovered, setIsChipHovered] = React.useState<boolean>(false);

  const openTooltip = (): void => setIsChipHovered(true);

  const closeTooltip = (): void => setIsChipHovered(false);

  return (
    <Tooltip
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
