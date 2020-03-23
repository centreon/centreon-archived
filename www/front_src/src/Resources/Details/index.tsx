import * as React from 'react';

import { Paper, makeStyles, Grid, Divider } from '@material-ui/core';

import {
  useCancelTokenSource,
  getData,
  useSnackbar,
  Loader,
  Severity as SnackbarSeverity,
} from '@centreon/ui';

import { labelSomethingWentWrong } from '../translatedLabels';
import { Status, Parent } from '../models';
import Header from './Header';
import Body from './Body';

const useStyles = makeStyles(() => {
  return {
    details: {
      height: '100%',
    },
    header: {
      padding: 10,
    },
  };
});

interface Props {
  resourceId: string | null;
  onClose;
}

interface ResourceDetails {
  name: string;
  status: Status;
  parent: Parent;
  criticality: number;
  output: string;
}

const useGet = ({ onSuccess, endpoint }): (() => Promise<unknown>) => {
  const { token, cancel } = useCancelTokenSource();
  const { showMessage } = useSnackbar();

  React.useEffect(() => {
    return (): void => cancel();
  }, []);

  return (): Promise<unknown> =>
    getData({
      endpoint,
      requestParams: { cancelToken: token },
    })
      .then((entity) => {
        onSuccess(entity);
      })
      .catch(() =>
        showMessage({
          message: labelSomethingWentWrong,
          severity: SnackbarSeverity.error,
        }),
      );
};

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
      <Grid container direction="column">
        <Grid item className={classes.header}>
          <Header details={details} onClickClose={onClose} />
        </Grid>
        <Grid item>
          <Divider />
        </Grid>
        <Grid item>
          <Body details={details} />
        </Grid>
      </Grid>
    </Paper>
  );
};

export default Details;
