import { useState } from 'react';

import { useTranslation } from 'react-i18next';

import { Grid } from '@mui/material';

import {
  useSnackbar,
  Dialog,
  SelectField,
  useRequest,
  TextField
} from '@centreon/ui';

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
  labelUnknown
} from '../../../translatedLabels';

import { submitResourceStatus } from './api';

interface Props {
  onClose: () => void;
  onSuccess: () => void;
  resource: Resource;
}

const SubmitStatusForm = ({
  resource,
  onClose,
  onSuccess
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const { showSuccessMessage } = useSnackbar();

  const [selectedStatusId, setSelectedStatusId] = useState(0);
  const [output, setOutput] = useState('');
  const [performanceData, setPerformanceData] = useState('');

  const serviceStatuses = [
    {
      id: 0,
      name: t(labelOk)
    },
    {
      id: 1,
      name: t(labelWarning)
    },
    {
      id: 2,
      name: t(labelCritical)
    },
    { id: 3, name: t(labelUnknown) }
  ];

  const statuses = {
    host: [
      {
        id: 0,
        name: t(labelUp)
      },
      { id: 1, name: t(labelDown) },
      { id: 2, name: t(labelUnreachable) }
    ],
    metaservice: serviceStatuses,
    service: serviceStatuses
  };

  const { sendRequest, sending } = useRequest({
    request: submitResourceStatus
  });

  const submitStatus = (): void => {
    sendRequest({
      output,
      performanceData,
      resource,
      statusId: selectedStatusId
    }).then(() => {
      showSuccessMessage(t(labelStatusSubmitted));
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
      open
      confirmDisabled={sending}
      labelCancel={t(labelCancel)}
      labelConfirm={t(labelSubmit)}
      labelTitle={t(labelSubmitStatus)}
      submitting={sending}
      onCancel={onClose}
      onClose={onClose}
      onConfirm={submitStatus}
    >
      <Grid container direction="column" spacing={1} style={{ minWidth: 500 }}>
        <Grid item>
          <SelectField
            fullWidth
            label={t(labelStatus)}
            options={statuses[resource.type]}
            selectedOptionId={selectedStatusId}
            onChange={changeSelectedStatusId}
          />
        </Grid>
        <Grid item>
          <TextField
            fullWidth
            ariaLabel={t(labelOutput)}
            label={t(labelOutput)}
            value={output}
            onChange={changeOutput}
          />
        </Grid>
        <Grid item>
          <TextField
            fullWidth
            ariaLabel={t(labelPerformanceData)}
            label={t(labelPerformanceData)}
            value={performanceData}
            onChange={changePerformanceData}
          />
        </Grid>
      </Grid>
    </Dialog>
  );
};

export default SubmitStatusForm;
