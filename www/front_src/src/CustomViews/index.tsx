import { FC } from 'react';

import 'react-grid-layout/css/styles.css';
import 'react-resizable/css/styles.css';
import { makeStyles } from 'tss-react/mui';

import Layout from './Layout';
import Toolbar from './Toolbar';

const useStyles = makeStyles()((theme) => ({
  toolbarContainer: {
    display: 'flex',
    flexDirection: 'column',
    padding: theme.spacing(0, 3),
    rowGap: theme.spacing(2),
  },
}));

const CustomViews: FC = () => {
  const { classes } = useStyles();

  return (
    <div className={classes.toolbarContainer}>
      <Toolbar />
      <Layout />
    </div>
  );
};

export default CustomViews;
