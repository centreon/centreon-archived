import * as React from 'react';

import { makeStyles } from '@material-ui/styles';

interface Props {
  children: React.ReactNode;
}

const useStyles = makeStyles(() => ({
  column: {
    display: 'flex',
    justifyContent: 'center',
    width: '100%',
  },
}));

const IconColumn = ({ children }: Props): JSX.Element => {
  const classes = useStyles();

  return <div className={classes.column}>{children}</div>;
};

export default IconColumn;
