import * as React from 'react';

import { Button, ButtonProps, Grid } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';

import IconDowntime from './columns/icons/Downtime';
import { labelAcknowledge, labelDowntime } from './translatedLabels';
import { Resource } from './models';
import AcknowledgeForm from './forms/Acknowledge';
import DowntimeForm from './forms/Downtime';

interface Props {
  disabled: boolean;
  resourcesToAcknowledge: Array<Resource>;
  onPrepareToAcknowledge;
  onCancelAcknowledge;
  resourcesToSetDowntime: Array<Resource>;
  onPrepareToSetDowntime;
  onCancelSetDowntime;
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
  onSuccess,
}: Props & Omit<ButtonProps, 'disabled'>): JSX.Element => {
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

export default Actions;
