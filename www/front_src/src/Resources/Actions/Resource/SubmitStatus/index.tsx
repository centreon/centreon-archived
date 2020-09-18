import * as React from 'react';

import { useTranslation } from 'react-i18next';

import {
  useSnackbar,
  Dialog,
  SelectField,
  useRequest,
  Severity,
  TextField,
} from '@centreon/ui';

import { Grid } from '@material-ui/core';
import { Resource } from '../../../models';
import {
  labelCancel,
  labelSubmit,
  labelSubmitStatus,
  labelStatus,
  labelStatusSubmitted,
  labelPerformanceData,
  labelOutput,
  labelUp,
  labelDown,
  labelUnreachable,
  labelOk,
  labelWarning,
  labelCritical,
  labelUnknown,
} from '../../../translatedLabels';
import { submitResourceStatus } from './api';

interface Props {
  resource: Resource;
  onClose: () => void;
  onSuccess: () => void;
}

const SubmitStatusForm = ({
  resource,
  onClose,
  onSuccess,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const { showMessage } = useSnackbar();

  const [selectedStatusId, setSelectedStatusId] = React.useState(0);
  const [output, setOutput] = React.useState('');
  const [performanceData, setPerformanceData] = React.useState('');

  const statuses = {
    host: [
      {
        id: 0,
        name: t(labelUp),
      },
      { id: 1, name: t(labelDown) },
      { id: 2, name: t(labelUnreachable) },
    ],
    service: [
      {
        id: 0,
        name: t(labelOk),
      },
      {
        id: 1,
        name: t(labelWarning),
      },
      {
        id: 2,
        name: t(labelCritical),
      },
      { id: 3, name: t(labelUnknown) },
    ],
  };

  const { sendRequest, sending } = useRequest({
    request: submitResourceStatus,
  });

  const submitStatus = (): void => {
    sendRequest({
      resource,
      statusId: selectedStatusId,
      output,
      performanceData,
    }).then(() => {
      showMessage({
        message: t(labelStatusSubmitted),
        severity: Severity.success,
      });
      onSuccess();
    });
  };

  const changeSelectedStatusId = (event): void => {
    setSelectedStatusId(event.target.value);
  };

  const changeOutput = (event): void => {
    setOutput(event.target.value);
  };

  const changePerformanceData = (event): void => {
    setPerformanceData(event.target.value);
  };

  return (
    <Dialog
      labelCancel={t(labelCancel)}
      labelConfirm={t(labelSubmit)}
      labelTitle={t(labelSubmitStatus)}
      open
      onClose={onClose}
      onCancel={onClose}
      onConfirm={submitStatus}
      confirmDisabled={sending}
      submitting={sending}
    >
      <Grid direction="column" container spacing={1} style={{ minWidth: 300 }}>
        <Grid item>
          <SelectField
            options={statuses[resource.type]}
            label={t(labelStatus)}
            selectedOptionId={selectedStatusId}
            onChange={changeSelectedStatusId}
            fullWidth
          />
        </Grid>
        <Grid item>
          <TextField
            value={output}
            onChange={changeOutput}
            multiline
            label={t(labelOutput)}
            fullWidth
            rows={3}
          />
        </Grid>
        <Grid item>
          <TextField
            value={performanceData}
            onChange={changePerformanceData}
            multiline
            label={t(labelPerformanceData)}
            fullWidth
            rows={3}
          />
        </Grid>
      </Grid>
    </Dialog>
  );
};

export default SubmitStatusForm;
