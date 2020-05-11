import * as React from 'react';

import { isEmpty } from 'ramda';

import { Button, ButtonProps, Grid } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';
import IconCheck from '@material-ui/icons/Sync';

import { useCancelTokenSource, Severity, useSnackbar } from '@centreon/ui';

import IconDowntime from '../../icons/Downtime';
import {
  labelAcknowledge,
  labelDowntime,
  labelCheck,
  labelSomethingWentWrong,
  labelCheckCommandSent,
} from '../../translatedLabels';
import AcknowledgeForm from './Acknowledge';
import DowntimeForm from './Downtime';
import { checkResources } from '../../api';
import { useResourceContext } from '../../Context';

const ActionButton = (props: ButtonProps): JSX.Element => (
  <Button variant="contained" color="primary" size="small" {...props} />
);

const ResourceActions = (): JSX.Element => {
  const { cancel, token } = useCancelTokenSource();
  const { showMessage } = useSnackbar();

  const {
    resourcesToCheck,
    setSelectedResources,
    selectedResources,
    resourcesToAcknowledge,
    setResourcesToAcknowledge,
    resourcesToSetDowntime,
    setResourcesToSetDowntime,
    setResourcesToCheck,
  } = useResourceContext();

  const showError = (message): void =>
    showMessage({ message, severity: Severity.error });
  const showSuccess = (message): void =>
    showMessage({ message, severity: Severity.success });

  const hasResourcesToCheck = resourcesToCheck.length > 0;

  const confirmAction = (): void => {
    setSelectedResources([]);
    setResourcesToAcknowledge([]);
    setResourcesToSetDowntime([]);
    setResourcesToCheck([]);
  };

  React.useEffect(() => {
    if (!hasResourcesToCheck) {
      return;
    }

    checkResources({
      resources: resourcesToCheck,
      cancelToken: token,
    })
      .then(() => {
        confirmAction();
        showSuccess(labelCheckCommandSent);
      })
      .catch(() => showError(labelSomethingWentWrong));
  }, [resourcesToCheck]);

  React.useEffect(() => (): void => cancel(), []);

  const prepareToAcknowledge = (): void => {
    setResourcesToAcknowledge(selectedResources);
  };

  const prepareToSetDowntime = (): void => {
    setResourcesToSetDowntime(selectedResources);
  };

  const prepareToCheck = (): void => {
    setResourcesToCheck(selectedResources);
  };

  const cancelAcknowledge = (): void => {
    setResourcesToAcknowledge([]);
  };

  const cancelSetDowntime = (): void => {
    setResourcesToSetDowntime([]);
  };

  const disabled = isEmpty(selectedResources);

  return (
    <Grid container spacing={1}>
      <Grid item>
        <ActionButton
          disabled={disabled}
          startIcon={<IconAcknowledge />}
          onClick={prepareToAcknowledge}
        >
          {labelAcknowledge}
        </ActionButton>
      </Grid>
      <Grid item>
        <ActionButton
          disabled={disabled}
          startIcon={<IconDowntime />}
          onClick={prepareToSetDowntime}
        >
          {labelDowntime}
        </ActionButton>
      </Grid>
      <Grid item>
        <ActionButton
          disabled={disabled}
          startIcon={<IconCheck />}
          onClick={prepareToCheck}
        >
          {labelCheck}
        </ActionButton>
      </Grid>
      {resourcesToAcknowledge.length > 0 && (
        <AcknowledgeForm
          resources={resourcesToAcknowledge}
          onClose={cancelAcknowledge}
          onSuccess={confirmAction}
        />
      )}
      {resourcesToSetDowntime.length > 0 && (
        <DowntimeForm
          resources={resourcesToSetDowntime}
          onClose={cancelSetDowntime}
          onSuccess={confirmAction}
        />
      )}
    </Grid>
  );
};

export default ResourceActions;
