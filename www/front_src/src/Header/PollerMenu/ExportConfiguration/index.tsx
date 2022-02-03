import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { not } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { Button, Paper, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { getData, useRequest, useSnackbar, Dialog } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import {
  labelCancel,
  labelConfigurationExportedAndReloaded,
  labelExportAndReload,
  labelExportAndReloadTheConfiguration,
  labelExportConfiguration,
  labelExportingAndReloadingTheConfiguration,
  labelFailedToExportAndReloadConfiguration,
  labelThisWillExportAndReloadOnAllOfYourPlatform,
} from '../translatedLabels';

import { exportAndReloadConfigurationEndpoint } from './api/endpoints';

interface Props {
  setIsExportingConfiguration: (isExporting: boolean) => void;
  toggleDetailedView: () => void;
}

const useStyles = makeStyles((theme) => ({
  exportButton: {
    display: 'flex',
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
  toggleDetailedView,
}: Props): JSX.Element | null => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [askingBeforeExportConfiguration, setAskingBeforeExportConfiguration] =
    React.useState(false);
  const { sendRequest, sending } = useRequest({
    defaultFailureMessage: t(labelFailedToExportAndReloadConfiguration),
    request: getData,
  });
  const { showInfoMessage, showSuccessMessage } = useSnackbar();

  const { isExportButtonEnabled } = useAtomValue(userAtom);

  const askBeforeExportConfiguration = (): void => {
    setAskingBeforeExportConfiguration(true);
  };

  const closeConfirmDialog = (): void =>
    setAskingBeforeExportConfiguration(false);

  const confirmExportAndReload = (): void => {
    toggleDetailedView();
    showInfoMessage(t(labelExportingAndReloadingTheConfiguration));
    sendRequest({
      endpoint: exportAndReloadConfigurationEndpoint,
    }).then(() => {
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
            {t(labelThisWillExportAndReloadOnAllOfYourPlatform)}
          </Typography>
        </div>
      </Dialog>
    </>
  );
};

export default ExportConfiguration;
