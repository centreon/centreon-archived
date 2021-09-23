import * as React from 'react';

import { makeStyles } from '@material-ui/core';

import { ResourceDetails } from '../../../models';

import DetailsLine from './DetailsLine';

const useStyles = makeStyles((theme) => ({
  lastTimeWithNoIssue: {
    alignItems: 'center',
    columnGap: `${theme.spacing(1)}px`,
    display: 'grid',
    gridTemplateColumns: 'auto min-content',
  },
}));

interface Props {
  details: ResourceDetails;
}

const LastTimeWithNoIssue = ({ details }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.lastTimeWithNoIssue}>
      <DetailsLine line={`${details.duration}`} />
    </div>
  );
};

export default LastTimeWithNoIssue;
