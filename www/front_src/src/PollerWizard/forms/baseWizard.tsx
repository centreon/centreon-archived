import { ReactNode } from 'react';

import { makeStyles } from 'tss-react/mui';

import { Paper } from '@mui/material';

const useStyles = makeStyles()((theme) => ({
  page: {
    height: 'fit-content',
    margin: '0 auto',
    padding: theme.spacing(2, 0),
    width: '40%',
  },
}));

interface Props {
  children: ReactNode;
}

const BaseWizard = ({ children }: Props): JSX.Element => {
  const { classes } = useStyles();

  return <Paper className={classes.page}>{children}</Paper>;
};

export default BaseWizard;
