import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Button, makeStyles, Paper, Typography } from '@material-ui/core';

import { getData, useRequest, useSnackbar, Dialog } from '@centreon/ui';

import {
  labelCancel,
  labelConfigurationExportedAndReloaded,
  labelExportAndReload,
  labelExportAndReloadTheConfiguration,
  labelExportConfiguration,
  labelExportingAndReloadingTheConfiguration,
  labelFailedToExportAndReloadConfiguration,
  labelThisWillExportAndReloadOnTheFollowingPollers,
} from '../translatedLabels';

import { exportAndReloadConfigurationEndpoint } from './api/endpoints';

interface Props {
  setIsExportingConfiguration: (isExporting: boolean) => void;
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

const ExportConfiguration = ({
  setIsExportingConfiguration,
}: Props): JSX.Element => {
  const [askingBeforeExportConfiguration, setAskingBeforeExportConfiguration] =
    React.useState(false);
  const { sendRequest, sending } = useRequest({
    request: getData,
  });
  const { t } = useTranslation();
  const classes = useStyles();
  const { showInfoMessage, showSuccessMessage, showErrorMessage } =
    useSnackbar();

  const askBeforeExportConfiguration = (): void => {
    setAskingBeforeExportConfiguration(true);
  };

  const closeConfirmDialog = (): void =>
    setAskingBeforeExportConfiguration(false);

  const confirmExportAndReload = (): void => {
    showInfoMessage(t(labelExportingAndReloadingTheConfiguration));
    sendRequest(exportAndReloadConfigurationEndpoint)
      .then(() => {
        showSuccessMessage(t(labelConfigurationExportedAndReloaded));
      })
      .catch(() => {
        showErrorMessage(t(labelFailedToExportAndReloadConfiguration));
      });
    closeConfirmDialog();
  };

  React.useEffect(() => {
    setIsExportingConfiguration(sending);
  }, [sending]);

  const disableButton = sending;

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
        </div>
      </Dialog>
    </>
  );
};

export default ExportConfiguration;
