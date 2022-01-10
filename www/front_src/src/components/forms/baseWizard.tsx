import * as React from 'react';

import makeStyles from '@mui/styles/makeStyles';

const useStyles = makeStyles((theme) => ({
  page: {
    margin: '0 auto',
    padding: theme.spacing(2, 0),
    width: '50%',
  },
}));

interface Props {
  children: React.ReactNode;
}

const BaseWizard = ({ children }: Props): JSX.Element => {
  const classes = useStyles();

  return <div className={classes.page}>{children}</div>;
};

export default BaseWizard;
