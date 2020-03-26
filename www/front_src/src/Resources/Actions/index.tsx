import * as React from 'react';

import { Button, ButtonProps, Grid } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';
import IconCheck from '@material-ui/icons/Sync';

import { useCancelTokenSource, Severity, useSnackbar } from '@centreon/ui';

import IconDowntime from '../columns/icons/Downtime';
import {
  labelAcknowledge,
  labelDowntime,
  labelCheck,
  labelSomethingWentWrong,
  labelCheckCommandSent,
} from '../translatedLabels';
import { Resource } from '../models';
import AcknowledgeForm from './Acknowledge';
import DowntimeForm from './Downtime';
import { checkResources } from '../api';

interface Props {
  disabled: boolean;
  resourcesToAcknowledge: Array<Resource>;
  onPrepareToAcknowledge;
  onCancelAcknowledge;
  resourcesToSetDowntime: Array<Resource>;
  onPrepareToSetDowntime;
  onCancelSetDowntime;
  resourcesToCheck: Array<Resource>;
  onPrepareToCheck;
  onSuccess;
}

const ActionButton = (props: ButtonProps): JSX.Element => (
  <Button variant="contained" color="primary" size="small" {...props} />
);

const Actions = ({
  disabled,
  resourcesToAcknowledge,
  onPrepareToAcknowledge,
  onCancelAcknowledge,
  resourcesToSetDowntime,
  onPrepareToSetDowntime,
  onCancelSetDowntime,
  resourcesToCheck,
  onPrepareToCheck,
  onSuccess,
}: Props & Omit<ButtonProps, 'disabled'>): JSX.Element => {
  const { cancel, token } = useCancelTokenSource();
  const { showMessage } = useSnackbar();

  const showError = (message): void =>
    showMessage({ message, severity: Severity.error });
  const showSuccess = (message): void =>
    showMessage({ message, severity: Severity.success });

  const hasResourcesToCheck = resourcesToCheck.length > 0;

  React.useEffect(() => {
    if (!hasResourcesToCheck) {
      return;
    }

    checkResources({
      resources: resourcesToCheck,
      cancelToken: token,
    })
      .then(() => {
        showSuccess(labelCheckCommandSent);
        onSuccess();
      })
      .catch(() => showError(labelSomethingWentWrong));
  }, [resourcesToCheck]);

  React.useEffect(() => (): void => cancel(), []);

  return (
    <Grid container spacing={1}>
      <Grid item>
        <ActionButton
          disabled={disabled}
          startIcon={<IconAcknowledge />}
          onClick={onPrepareToAcknowledge}
        >
          {labelAcknowledge}
        </ActionButton>
      </Grid>
      <Grid item>
        <ActionButton
          disabled={disabled}
          startIcon={<IconDowntime />}
          onClick={onPrepareToSetDowntime}
        >
          {labelDowntime}
        </ActionButton>
      </Grid>
      <Grid item>
        <ActionButton
          disabled={disabled}
          startIcon={<IconCheck />}
          onClick={onPrepareToCheck}
        >
          {labelCheck}
        </ActionButton>
      </Grid>
      <AcknowledgeForm
        resources={resourcesToAcknowledge}
        onClose={onCancelAcknowledge}
        onSuccess={onSuccess}
      />
      <DowntimeForm
        resources={resourcesToSetDowntime}
        onClose={onCancelSetDowntime}
        onSuccess={onSuccess}
      />
    </Grid>
  );
};

export default React.memo(
  Actions,
  (prevProps, nextProps) =>
    prevProps.disabled === nextProps.disabled &&
    prevProps.resourcesToAcknowledge.length ===
      nextProps.resourcesToAcknowledge.length &&
    prevProps.resourcesToSetDowntime.length ===
      nextProps.resourcesToSetDowntime.length &&
    prevProps.resourcesToCheck.length === nextProps.resourcesToCheck.length,
);
