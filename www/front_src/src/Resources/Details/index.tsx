import * as React from 'react';

import { omit } from 'ramda';

import { Paper, makeStyles, Divider } from '@material-ui/core';

import Header from './Header';
import Body from './Body';
import useGet from '../useGet';
import { ResourceDetails } from './models';
import { ResourceEndpoints } from '../models';

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
  onClose: () => void;
  endpoints: ResourceEndpoints;
  openTabId: number;
}

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

const Details = ({
  endpoints,
  onClose,
  openTabId,
}: Props): JSX.Element | null => {
  const classes = useStyles();

  const [details, setDetails] = React.useState<ResourceDetails>();

  const { details: detailsEndpoint } = endpoints;

  const get = useGet({
    onSuccess: (entity) => setDetails(entity),
    endpoint: detailsEndpoint,
  });

  React.useEffect(() => {
    if (details !== undefined) {
      setDetails(undefined);
    }

    get();
  }, [detailsEndpoint]);

  return (
    <Paper elevation={5} className={classes.details}>
      <div className={classes.header}>
        <Header details={details} onClickClose={onClose} />
      </div>
      <Divider className={classes.divider} />
      <div className={classes.body}>
        <Body
          details={details}
          endpoints={omit(['details'], endpoints)}
          openTabId={openTabId}
        />
      </div>
    </Paper>
  );
};

export default Details;
