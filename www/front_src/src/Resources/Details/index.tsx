import * as React from 'react';

import {
  Paper,
  makeStyles,
  Grid,
  Tabs,
  Tab,
  Typography,
  Card,
  CardContent,
} from '@material-ui/core';

import {
  useCancelTokenSource,
  getData,
  useSnackbar,
  Loader,
  StatusChip,
  SeverityCode,
  Severity as SnackbarSeverity,
} from '@centreon/ui';

import { labelSomethingWentWrong } from '../translatedLabels';
import { Status, Parent, Severity } from '../models';

const useStyles = makeStyles(() => ({
  details: {
    height: '100%',
  },
  header: {
    padding: 10,
  },
}));

interface Props {
  resourceId?: string;
}

interface ResourceDetails {
  name: string;
  status: Status;
  parent: Parent;
  severity: Severity;
}

const useGet = ({ dispatch, endpoint }) => {
  const { token, cancel } = useCancelTokenSource();
  const [, setEntity] = dispatch;
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
        setEntity(entity);
      })
      .catch(() =>
        showMessage({
          message: labelSomethingWentWrong,
          severity: SnackbarSeverity.error,
        }),
      );
};

interface ContentProps {
  details: ResourceDetails;
}

const Header = ({ details }: ContentProps): JSX.Element => (
  <Grid container item spacing={2} alignItems="center">
    <Grid item>
      <StatusChip
        severityCode={details.status.severity_code}
        label={details.status.name}
      />
    </Grid>
    <Grid item style={{ flexGrow: 1 }}>
      <Grid container direction="column">
        <Grid item>
          <Typography>{details.name}</Typography>
        </Grid>
        {details.parent && (
          <Grid item container spacing={1}>
            <Grid item>
              <StatusChip severityCode={details.parent.status.severity_code} />
            </Grid>
            <Grid item>
              <Typography variant="caption">{details.parent.name}</Typography>
            </Grid>
          </Grid>
        )}
      </Grid>
    </Grid>
    <Grid item>
      <StatusChip
        severityCode={SeverityCode.None}
        label={details.severity?.level.toString()}
      />
    </Grid>
  </Grid>
);

const Body = ({ details }: ContentProps) => {
  const [selectedTabId, setSelectedTabId] = React.useState(0);

  const changeSelectedTabId = (_, id): void => {
    setSelectedTabId(id);
  };
  return (
    <>
      <Tabs
        variant="fullWidth"
        value={selectedTabId}
        indicatorColor="primary"
        textColor="primary"
        onChange={changeSelectedTabId}
      >
        <Tab label="Details" />
        <Tab label="Graph" />
      </Tabs>
      {selectedTabId === 0 && (
        <>
          <Card>
            <CardContent />
          </Card>
        </>
      )}
    </>
  );
};

const Details = ({ resourceId }: Props): JSX.Element | null => {
  const classes = useStyles();

  const detailsDispatch = React.useState<ResourceDetails>();

  const get = useGet({
    dispatch: detailsDispatch,
    endpoint: 'http://localhost:5000/api/beta/resource',
  });

  React.useEffect(() => {
    if (resourceId !== undefined) {
      get();
    }
  }, [resourceId]);

  const [details] = detailsDispatch;

  if (resourceId === undefined) {
    return null;
  }

  if (details === undefined) {
    return <Loader />;
  }

  return (
    <Paper variant="outlined" elevation={2} className={classes.details}>
      <Grid container direction="column">
        <Grid item className={classes.header}>
          <Header details={details} />
        </Grid>
        <Grid item>
          <Body details={details} />
        </Grid>
      </Grid>
    </Paper>
  );
};

export default Details;
