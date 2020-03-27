import * as React from 'react';

import { Paper, makeStyles, Divider } from '@material-ui/core';

import Header from './Header';
import Body from './Body';
import useGet from '../useGet';
import { ResourceDetails } from './models';

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

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

const Details = ({ endpoint, onClose }: Props): JSX.Element | null => {
  const classes = useStyles();

  const [details, setDetails] = React.useState<ResourceDetails>();

  const get = useGet({
    onSuccess: (entity) => setDetails(entity),
    endpoint: 'http://localhost:5000/api/beta/resource',
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
