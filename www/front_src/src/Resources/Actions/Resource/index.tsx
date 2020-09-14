import * as React from 'react';

import { isEmpty } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Button, ButtonProps, Grid } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';
import IconCheck from '@material-ui/icons/Sync';

import { useCancelTokenSource, Severity, useSnackbar } from '@centreon/ui';

import IconDowntime from '../../icons/Downtime';
import {
  labelAcknowledge,
  labelSetDowntime,
  labelCheck,
  labelSomethingWentWrong,
  labelCheckCommandSent,
} from '../../translatedLabels';
import AcknowledgeForm from './Acknowledge';
import DowntimeForm from './Downtime';
import { useResourceContext } from '../../Context';
import useAclQuery from './aclQuery';
import { checkResources } from '../api';

const ActionButton = (props: ButtonProps): JSX.Element => (
  <Button variant="contained" color="primary" size="small" {...props} />
);

const ResourceActions = (): JSX.Element => {
  const { t } = useTranslation();
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

  const { canAcknowledge, canDowntime, canCheck } = useAclQuery();

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
        showSuccess(t(labelCheckCommandSent));
      })
      .catch(() => showError(t(labelSomethingWentWrong)));
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

  const noResourcesSelected = isEmpty(selectedResources);
  const disableAcknowledge =
    noResourcesSelected || !canAcknowledge(selectedResources);
  const disableDowntime =
    noResourcesSelected || !canDowntime(selectedResources);
  const disableCheck = noResourcesSelected || !canCheck(selectedResources);

  return (
    <Grid container spacing={1}>
      <Grid item>
        <ActionButton
          disabled={disableAcknowledge}
          startIcon={<IconAcknowledge />}
          onClick={prepareToAcknowledge}
        >
          {t(labelAcknowledge)}
        </ActionButton>
      </Grid>
      <Grid item>
        <ActionButton
          disabled={disableDowntime}
          startIcon={<IconDowntime />}
          onClick={prepareToSetDowntime}
        >
          {t(labelSetDowntime)}
        </ActionButton>
      </Grid>
      <Grid item>
        <ActionButton
          disabled={disableCheck}
          startIcon={<IconCheck />}
          onClick={prepareToCheck}
        >
          {t(labelCheck)}
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
