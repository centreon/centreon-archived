import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Tooltip, useMediaQuery, useTheme } from '@material-ui/core';

import { IconButton } from '@centreon/ui';

import ActionButton from '../ActionButton';
import { labelActionNotPermitted } from '../../translatedLabels';

interface Props {
  disabled: boolean;
  icon: JSX.Element;
  label: string;
  onClick: () => void;
  permitted?: boolean;
}

const ResourceActionButton = ({
  icon,
  label,
  onClick,
  disabled,
  permitted = true,
}: Props): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();

  const displayCondensed = Boolean(useMediaQuery(theme.breakpoints.down(1100)));

  const title = permitted ? label : `${label} (${t(labelActionNotPermitted)})`;

  if (displayCondensed) {
    return (
      <IconButton
        data-testid={label}
        disabled={disabled}
        title={title}
        onClick={onClick}
      >
        {icon}
      </IconButton>
    );
  }

  return (
    <Tooltip title={permitted ? '' : labelActionNotPermitted}>
      <span>
        <ActionButton
          data-testid={label}
          disabled={disabled}
          startIcon={icon}
          variant="contained"
          onClick={onClick}
        >
          {label}
        </ActionButton>
      </span>
    </Tooltip>
  );
};

export default ResourceActionButton;
