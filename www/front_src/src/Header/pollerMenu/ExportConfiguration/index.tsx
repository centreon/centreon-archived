import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { not } from 'ramda';

import { Button, makeStyles, Paper, Typography } from '@material-ui/core';

import { getData, useRequest, useSnackbar, Dialog } from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import {
  labelCancel,
  labelConfigurationExportedAndReloaded,
  labelExportAndReload,
  labelExportAndReloadTheConfiguration,
  labelExportConfiguration,
  labelExportingAndReloadingTheConfiguration,
  labelFailedToExportAndReloadConfiguration,
  labelThisWillExportAndReloadOnAllOfYourPollers,
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
}: Props): JSX.Element | null => {
  const [askingBeforeExportConfiguration, setAskingBeforeExportConfiguration] =
    React.useState(false);

  const { t } = useTranslation();
  const { isExportButtonEnabled } = useUserContext();
  const { sendRequest, sending } = useRequest({
    defaultFailureMessage: t(labelFailedToExportAndReloadConfiguration),
    request: getData,
  });
  const classes = useStyles();
  const { showInfoMessage, showSuccessMessage } = useSnackbar();

  const askBeforeExportConfiguration = (): void => {
    setAskingBeforeExportConfiguration(true);
  };

  const closeConfirmDialog = (): void =>
    setAskingBeforeExportConfiguration(false);

  const confirmExportAndReload = (): void => {
    showInfoMessage(t(labelExportingAndReloadingTheConfiguration));
    sendRequest(exportAndReloadConfigurationEndpoint).then(() => {
      showSuccessMessage(t(labelConfigurationExportedAndReloaded));
    });
    closeConfirmDialog();
  };

  React.useEffect(() => {
    setIsExportingConfiguration(sending);
  }, [sending]);

  if (not(isExportButtonEnabled)) {
    return null;
  }

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
            {t(labelThisWillExportAndReloadOnAllOfYourPollers)}
          </Typography>
        </div>
      </Dialog>
    </>
  );
};

export default ExportConfiguration;
