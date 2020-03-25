import * as React from 'react';

import { Paper, makeStyles, Grid, Divider } from '@material-ui/core';

import { Loader } from '@centreon/ui';

import { Status, Parent, Downtime, Acknowledgement } from '../models';
import Header from './Header';
import Body from './Body';
import useGet from '../useGet';

const useStyles = makeStyles(() => {
  return {
    details: {
      height: '100%',
      display: 'grid',
      gridTemplate: 'auto auto 1fr / 1fr',
    },
    header: {
      gridArea: '1 / 1 / 2 / 1',
      padding: 8,
    },
    divider: {
      gridArea: '2 / 1 / 3 / 1',
    },
    body: {
      gridArea: '3 / 1 / 4 / 1',
    },
  };
});

interface Props {
  resourceId: string | null;
  onClose;
}

export interface ResourceDetails {
  name: string;
  status: Status;
  parent: Parent;
  criticality: number;
  output: string;
  downtimes?: Array<Downtime>;
  acknowledgement?: Acknowledgement;
  duration: string;
  tries: string;
}

export interface DetailsSectionProps {
  details: ResourceDetails;
}

const Details = ({ resourceId, onClose }: Props): JSX.Element | null => {
  const classes = useStyles();

  const [details, setDetails] = React.useState<ResourceDetails>();

  const get = useGet({
    onSuccess: (entity) => setDetails(entity),
    endpoint: 'http://localhost:5000/api/beta/resource',
  });

  React.useEffect(() => {
    if (resourceId !== null) {
      get();
    }
  }, [resourceId]);

  if (resourceId === null) {
    return null;
  }

  if (details === undefined) {
    return <Loader />;
  }

  return (
    <Paper variant="outlined" elevation={2} className={classes.details}>
      <div className={classes.header}>
        <Header details={details} onClickClose={onClose} />
      </div>
      <Divider className={classes.divider} />
      <div className={classes.body}>
        <Body details={details} />
      </div>
    </Paper>
  );
};

export default Details;
