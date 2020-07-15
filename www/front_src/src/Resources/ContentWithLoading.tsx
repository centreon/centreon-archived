import React from 'react';

import { CircularProgress, makeStyles } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  loadingIndicator: {
    width: '100%',
    heihgt: '100%',
    position: 'absolute',
    top: '50%',
    left: '50%',
  },
}));

interface Props {
  loading: boolean;
  children: React.ReactElement;
}

const ContentWithLoading = ({ loading, children }: Props): JSX.Element => {
  const classes = useStyles();

  return loading ? (
    <CircularProgress className={classes.loadingIndicator} />
  ) : (
    children
  );
};

export default ContentWithLoading;
