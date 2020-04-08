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
  ...props
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip title={title} onClick={onClick} aria-label={ariaLabel}>
      <span>
        <IconButton className={classes.button} color="primary" {...props} />
      </span>
    </Tooltip>
  );
};

export default ActionButton;
