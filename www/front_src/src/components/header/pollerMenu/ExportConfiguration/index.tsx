import * as React from 'react';

import { TFunction, useTranslation } from 'react-i18next';
import { equals, filter, isEmpty, not, propEq } from 'ramda';

import {
  Button,
  List,
  makeStyles,
  Paper,
  Typography,
  ListItem,
  ListItemText,
} from '@material-ui/core';

import {
  getData,
  useRequest,
  ListingModel,
  useSnackbar,
  Dialog,
} from '@centreon/ui';

import {
  labelCancel,
  labelExportAndReload,
  labelExportAndReloadSucceeded,
  labelExportAndReloadTheConfiguration,
  labelExportConfiguration,
  labelExportingAndReloadingTheConfiguration,
  labelThisWillExportAndReloadOnTheFollowingPollers,
} from '../translatedLabels';

import {
  listMonitoringServersDecoder,
  statusMessageDecoder,
} from './api/decoders';
import { MonitoringServer, Status, StatusMessage } from './models';
import { buildMonitoringServersEndpoint } from './api';
import { exportAndReloadConfigurationEndpoint } from './api/endpoints';

interface Props {
  setIsExportingConfiguration: (isExporting: boolean) => void;
  total: number;
}

const useStyles = makeStyles((theme) => ({
  exportButton: {
    marginTop: theme.spacing(1),
  },
  pollerText: {
    margin: theme.spacing(0),
  },
  pollersList: {
    maxHeight: '50vh',
    overflowY: 'auto',
  },
}));

interface ShowExportAndReloadMessagesProps {
  monitoringServers: Array<MonitoringServer>;
  result: Array<StatusMessage>;
  statusToFilterOn: Status;
  t: TFunction;
}

const showExportAndReloadMessages = ({
  result,
  monitoringServers,
  statusToFilterOn,
  t,
}: ShowExportAndReloadMessagesProps): Record<string, string> =>
  result.reduce((acc, statusMessage, idx) => {
    if (equals(statusMessage.status, statusToFilterOn)) {
      return acc;
    }

    const monitoringServer = monitoringServers[idx];

    const isFilteredOnBySuccess = equals(statusToFilterOn, Status.ok);

    return {
      ...acc,
      [monitoringServer.name]: isFilteredOnBySuccess
        ? t(labelExportAndReloadSucceeded)
        : statusMessage.message,
    };
  }, {});

const ExportConfiguration = ({
  total,
  setIsExportingConfiguration,
}: Props): JSX.Element => {
  const [monitoringServers, setMonitoringServers] = React.useState<
    Array<MonitoringServer>
  >([]);
  const [askingBeforeExportConfiguration, setAskingBeforeExportConfiguration] =
    React.useState(false);
  const { sendRequest: sendMonitoringServersRequest, sending } = useRequest<
    ListingModel<MonitoringServer>
  >({
    decoder: listMonitoringServersDecoder,
    request: getData,
  });
  const { sendRequest: sendExportAndReloadConfigurationRequest } =
    useRequest<StatusMessage>({
      decoder: statusMessageDecoder,
      request: getData,
    });
  const { t } = useTranslation();
  const classes = useStyles();
  const { showSuccessMessages, showInfoMessage, showErrorMessages } =
    useSnackbar();

  const loadMonitoringServers = (): Promise<ListingModel<MonitoringServer>> =>
    sendMonitoringServersRequest(buildMonitoringServersEndpoint(total));

  const askBeforeExportConfiguration = (): void => {
    setAskingBeforeExportConfiguration(true);
  };

  const closeConfirmDialog = (): void =>
    setAskingBeforeExportConfiguration(false);

  const exportAndReload = (pollerId: number): Promise<StatusMessage> =>
    sendExportAndReloadConfigurationRequest(
      exportAndReloadConfigurationEndpoint(pollerId),
    );

  const confirmExportAndReload = (): void => {
    showInfoMessage(t(labelExportingAndReloadingTheConfiguration));
    Promise.all([
      ...monitoringServers.map(({ id }) => exportAndReload(id)),
    ]).then((result: Array<StatusMessage>) => {
      const errorStatusMessages = filter(
        propEq('status', Status.error),
        result,
      );

      const successStatusMessages = filter(propEq('status', Status.ok), result);

      const hasExportSucceeded = not(isEmpty(successStatusMessages));

      const hasErrorMessages = not(isEmpty(errorStatusMessages));

      if (hasExportSucceeded) {
        showSuccessMessages(
          showExportAndReloadMessages({
            monitoringServers,
            result,
            statusToFilterOn: Status.ok,
            t,
          }),
        );
      }

      if (hasErrorMessages) {
        showErrorMessages(
          showExportAndReloadMessages({
            monitoringServers,
            result,
            statusToFilterOn: Status.error,
            t,
          }),
        );
      }
    });
    closeConfirmDialog();
  };

  React.useEffect(() => {
    setIsExportingConfiguration(askingBeforeExportConfiguration);
  }, [askingBeforeExportConfiguration]);

  React.useEffect((): void => {
    loadMonitoringServers().then(({ result }) => {
      setMonitoringServers(result);
    });
  }, [total]);

  const disableButton = isEmpty(monitoringServers) || sending;

  return (
    <>
      <Paper className={classes.exportButton}>
        <Button
          disabled={disableButton}
          size="small"
          variant="contained"
          onClick={askBeforeExportConfiguration}
        >
          {t(labelExportConfiguration)}
        </Button>
      </Paper>
      <Dialog
        labelCancel={t(labelCancel)}
        labelConfirm={t(labelExportAndReload)}
        labelTitle={t(labelExportAndReloadTheConfiguration)}
        open={askingBeforeExportConfiguration}
        onCancel={closeConfirmDialog}
        onClose={closeConfirmDialog}
        onConfirm={confirmExportAndReload}
      >
        <div>
          <Typography>
            {t(labelThisWillExportAndReloadOnTheFollowingPollers)}:
          </Typography>
          <List dense className={classes.pollersList}>
            {monitoringServers.map(({ name }) => (
              <ListItem key={name}>
                <ListItemText className={classes.pollerText} primary={name} />
              </ListItem>
            ))}
          </List>
        </div>
      </Dialog>
    </>
  );
};

export default ExportConfiguration;
