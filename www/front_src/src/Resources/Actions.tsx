import * as React from 'react';

import { Button, ButtonProps, withStyles } from '@material-ui/core';
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
/*
interface ActionButtonProps extends ButtonProps, Props {}

const ActionButton = withStyles((theme) => ({
  root: {
    margin: theme.spacing(0, 1),
  },
}))((props: ActionButtonProps) => (
  <Button variant="contained" color="primary" size="small" {...props} />
));
*/
const ActionButton = (props: ButtonProps): JSX.Element => (
  <Button
    style={{ margin: '0 8px' }}
    variant="contained"
    color="primary"
    size="small"
    {...props}
  />
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
    <>
      <ActionButton
        disabled={disabled}
        startIcon={<IconAcknowledge />}
        onClick={onPrepareToAcknowledge}
      >
        {labelAcknowledge}
      </ActionButton>
      <ActionButton
        disabled={disabled}
        startIcon={<IconDowntime />}
        onClick={onPrepareToSetDowntime}
      >
        {labelDowntime}
      </ActionButton>
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
    </>
  );
};

export default Actions;
