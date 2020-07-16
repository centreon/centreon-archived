import React from 'react';

import clsx from 'clsx';

import { CircularProgress, makeStyles } from '@material-ui/core';

const useStyles = makeStyles(() => ({
  alignCenter: {
    width: '100%',
    height: '100%',
    position: 'absolute',
    top: '50%',
    left: '50%',
  },
}));

interface Props {
  loading: boolean;
  children: React.ReactElement;
  alignCenter?: boolean;
  loadingIndicatorSize?: number;
}

const ContentWithLoading = ({
  loading,
  children,
  loadingIndicatorSize = undefined,
  alignCenter = true,
}: Props): JSX.Element => {
  const classes = useStyles();

  if (loading) {
    return (
      <CircularProgress
        className={clsx({ [classes.alignCenter]: alignCenter })}
        size={loadingIndicatorSize}
      />
    );
  }

  return children;
};

export default ContentWithLoading;
