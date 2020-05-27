import * as React from 'react';

import {
  makeStyles,
  IconButton,
  IconButtonProps,
  Tooltip,
} from '@material-ui/core';

const useStyles = makeStyles(() => ({
  button: {
    padding: 0,
  },
}));

type Props = {
  title: string;
  onClick: () => void;
  ariaLabel: string;
} & IconButtonProps;

const ActionButton = ({
  title,
  onClick,
  ariaLabel,
  disabled,
  ...props
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip title={title} aria-label={ariaLabel}>
      <span>
        <IconButton
          className={classes.button}
          color="primary"
          onClick={onClick}
          disabled={disabled}
          {...props}
        />
      </span>
    </Tooltip>
  );
};

export default ActionButton;
