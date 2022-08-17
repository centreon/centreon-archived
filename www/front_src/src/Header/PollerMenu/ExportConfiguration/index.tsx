import { useState, useEffect } from 'react';

import { useTranslation } from 'react-i18next';
import { equals, not } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { Button, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { getData, useRequest, useSnackbar, Dialog } from '@centreon/ui';
import { userAtom, ThemeMode } from '@centreon/ui-context';

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
    '&:hover': {
      background: theme.palette.grey[500],
    },
    backgroundColor: equals(theme.palette.mode, ThemeMode.dark)
      ? theme.palette.background.default
      : theme.palette.primary.main,
    border: '1px solid white',
    color: theme.palette.common.white,
    display: 'flex',
    fontSize: theme.typography.body2.fontSize,
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
    useState(false);
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

  useEffect(() => {
    setIsExportingConfiguration(sending);
  }, [sending]);

  if (not(isExportButtonEnabled)) {
    return null;
  }

  const disableButton = sending;

  return (
    <>
      <Button
        className={classes.exportButton}
        data-testid={labelExportConfiguration}
        disabled={disableButton}
        size="small"
        onClick={askBeforeExportConfiguration}
      >
        {t(labelExportConfiguration)}
      </Button>
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
