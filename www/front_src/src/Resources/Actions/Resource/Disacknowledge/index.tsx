import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { propEq } from 'ramda';

import { Alert } from '@material-ui/lab';
import { FormControlLabel, Checkbox, Grid } from '@material-ui/core';

import { Severity, useSnackbar, useRequest, Dialog } from '@centreon/ui';

import {
  labelCancel,
  labelDisacknowledgeServices,
  labelDisacknowledge,
  labelDisacknowledgementCommandSent,
} from '../../../translatedLabels';
import { Resource } from '../../../models';
import useAclQuery from '../aclQuery';

import { disacknowledgeResources } from './api';

interface Props {
  onClose;
  onSuccess;
  resources: Array<Resource>;
}

const DisacknowledgeForm = ({
  resources,
  onClose,
  onSuccess,
}: Props): JSX.Element | null => {
  const { t } = useTranslation();
  const { showMessage } = useSnackbar();
  const [disacknowledgeAttachedResources, setDisacknowledgeAttachedResources] =
    React.useState(true);

  const {
    sendRequest: sendDisacknowledgeResources,
    sending: sendingDisacknowledgeResources,
  } = useRequest({
    request: disacknowledgeResources,
  });

  const { getDisacknowledgementDeniedTypeAlert, canDisacknowledgeServices } =
    useAclQuery();

  const deniedTypeAlert = getDisacknowledgementDeniedTypeAlert(resources);

  React.useEffect(() => {
    if (canDisacknowledgeServices()) {
      return;
    }

    setDisacknowledgeAttachedResources(false);
  }, []);

  const showSuccess = (message): void =>
    showMessage({ message, severity: Severity.success });

  const submitDisacknowledge = (): void => {
    sendDisacknowledgeResources({
      disacknowledgeAttachedResources,
      resources,
    }).then(() => {
      showSuccess(t(labelDisacknowledgementCommandSent));
      onSuccess();
    });
  };

  const changeDisacknowledgeAttachedRessources = (event): void => {
    setDisacknowledgeAttachedResources(Boolean(event.target.checked));
  };

  const hasHosts = resources.find(propEq('type', 'host'));

  return (
    <Dialog
      open
      confirmDisabled={sendingDisacknowledgeResources}
      labelCancel={t(labelCancel)}
      labelConfirm={t(labelDisacknowledge)}
      labelTitle={t(labelDisacknowledge)}
      submitting={sendingDisacknowledgeResources}
      onCancel={onClose}
      onClose={onClose}
      onConfirm={submitDisacknowledge}
    >
      <Grid container direction="column" spacing={1}>
        {deniedTypeAlert && (
          <Grid item>
            <Alert severity="warning">{deniedTypeAlert}</Alert>
          </Grid>
        )}
        {hasHosts && (
          <Grid item>
            <FormControlLabel
              control={
                <Checkbox
                  checked={
                    canDisacknowledgeServices() &&
                    disacknowledgeAttachedResources
                  }
                  color="primary"
                  disabled={!canDisacknowledgeServices()}
                  inputProps={{ 'aria-label': t(labelDisacknowledgeServices) }}
                  size="small"
                  onChange={changeDisacknowledgeAttachedRessources}
                />
              }
              label={t(labelDisacknowledgeServices)}
            />
          </Grid>
        )}
      </Grid>
    </Dialog>
  );
};

export default DisacknowledgeForm;
