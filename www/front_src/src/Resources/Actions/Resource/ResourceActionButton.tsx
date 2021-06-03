import * as React from 'react';

import { useMediaQuery, useTheme } from '@material-ui/core';

import { IconButton } from '@centreon/ui';

import ActionButton from '../ActionButton';

interface Props {
  disabled: boolean;
  icon: JSX.Element;
  label: string;
  onClick: () => void;
}

const ResourceActionButton = ({
  icon,
  label,
  onClick,
  disabled,
}: Props): JSX.Element => {
  const theme = useTheme();
  const displayCondensed = Boolean(useMediaQuery(theme.breakpoints.down(1100)));

  if (displayCondensed) {
    return (
      <IconButton disabled={disabled} title={label} onClick={onClick}>
        {icon}
      </IconButton>
    );
  }

  return (
    <ActionButton
      disabled={disabled}
      startIcon={icon}
      variant="contained"
      onClick={onClick}
    >
      {label}
    </ActionButton>
  );
};

export default ResourceActionButton;
