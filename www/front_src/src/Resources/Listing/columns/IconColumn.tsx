import { ReactNode } from 'react';

import { makeStyles } from '@mui/styles';

interface Props {
  children: ReactNode;
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
