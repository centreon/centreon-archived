import * as React from 'react';

import { Paper, makeStyles, Divider } from '@material-ui/core';

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
  endpoint: string | null;
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
  poller_name?: string;
  timezone?: string;
  last_state_change: string;
  last_check: string;
  next_check: string;
  active_checks: boolean;
  execution_time: number;
  latency: number;
  flapping: boolean;
  percent_state_change: number;
  last_notification: string;
  notification_number: number;
  performance_data: string;
  check_command: string;
}

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

const Details = ({ endpoint, onClose }: Props): JSX.Element | null => {
  const classes = useStyles();

  const [details, setDetails] = React.useState<ResourceDetails>();

  const get = useGet({
    onSuccess: (entity) => setDetails(entity),
    endpoint,
  });

  React.useEffect(() => {
    if (details !== undefined) {
      setDetails(undefined);
    }

    get();
  }, [endpoint]);

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
