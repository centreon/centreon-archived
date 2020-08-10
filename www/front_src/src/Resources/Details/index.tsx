import * as React from 'react';

import { omit } from 'ramda';

import { Paper, makeStyles, Divider } from '@material-ui/core';

import { getData, useRequest } from '@centreon/ui';

import Header from './Header';
import Body from './Body';
import { ResourceDetails } from './models';
import { ResourceEndpoints } from '../models';
import { useResourceContext } from '../Context';

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

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

const Details = (): JSX.Element | null => {
  const classes = useStyles();

  const [details, setDetails] = React.useState<ResourceDetails>();

  const {
    detailsTabIdToOpen,
    setDefaultDetailsTabIdToOpen,
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
    listing,
  } = useResourceContext();

  const {
    details: detailsEndpoint,
  } = selectedDetailsEndpoints as ResourceEndpoints;

  const clearSelectedResource = (): void => {
    setSelectedDetailsEndpoints(null);
  };

  const { sendRequest } = useRequest<ResourceDetails>({
    request: getData,
  });

  React.useEffect(() => {
    if (details !== undefined) {
      setDetails(undefined);
    }

    sendRequest(detailsEndpoint).then((retrievedDetails) =>
      setDetails(retrievedDetails),
    );
  }, [detailsEndpoint, listing]);

  return (
    <Paper elevation={5} className={classes.details}>
      <div className={classes.header}>
        <Header details={details} onClickClose={clearSelectedResource} />
      </div>
      <Divider className={classes.divider} />
      <div className={classes.body}>
        <Body
          details={details}
          endpoints={omit(['details'], selectedDetailsEndpoints)}
          openTabId={detailsTabIdToOpen}
          onSelectTab={setDefaultDetailsTabIdToOpen}
        />
      </div>
    </Paper>
  );
};

export default Details;
