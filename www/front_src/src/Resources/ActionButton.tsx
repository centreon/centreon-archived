import * as React from 'react';

import {
  makeStyles,
  IconButton,
  IconButtonProps,
  Tooltip,
} from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  button: {
    padding: theme.spacing(0.5),
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
      <IconButton className={classes.button} color="primary" {...props} />
    </Tooltip>
  );
};

export default ActionButton;
