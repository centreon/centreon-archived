import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Severity, useSnackbar, useRequest, Dialog } from '@centreon/ui';

import { propEq } from 'ramda';
import { Alert } from '@material-ui/lab';
import { FormControlLabel, Checkbox, Grid } from '@material-ui/core';
import {
  labelCancel,
  labelDisacknowledgeServices,
  labelDisacknowledge,
  labelDisacknowledgementCommandSent,
} from '../../../translatedLabels';
import { Resource } from '../../../models';
import { disacknowledgeResources } from './api';
import useAclQuery from '../aclQuery';

interface Props {
  resources: Array<Resource>;
  onClose;
  onSuccess;
}

const DisacknowledgeForm = ({
  resources,
  onClose,
  onSuccess,
}: Props): JSX.Element | null => {
  const { t } = useTranslation();
  const { showMessage } = useSnackbar();
  const [
    disacknowledgeAttachedResources,
    setDisacknowledgeAttachedResources,
  ] = React.useState(true);

  const {
    sendRequest: sendDisacknowledgeResources,
    sending: sendingDisacknowledgeResources,
  } = useRequest({
    request: disacknowledgeResources,
  });

  const {
    getDisacknowledgementDeniedTypeAlert,
    canDisacknowledgeServices,
  } = useAclQuery();

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
      resources,
      disacknowledgeAttachedResources,
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
      labelCancel={t(labelCancel)}
      labelConfirm={t(labelDisacknowledge)}
      labelTitle={t(labelDisacknowledge)}
      open
      onClose={onClose}
      onCancel={onClose}
      onConfirm={submitDisacknowledge}
      confirmDisabled={sendingDisacknowledgeResources}
      submitting={sendingDisacknowledgeResources}
    >
      <Grid direction="column" container spacing={1}>
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
                  disabled={!canDisacknowledgeServices()}
                  inputProps={{ 'aria-label': t(labelDisacknowledgeServices) }}
                  color="primary"
                  onChange={changeDisacknowledgeAttachedRessources}
                  size="small"
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
