import * as React from 'react';

import { Button } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';

import { labelAcknowledge } from './translatedLabels';
import { Resource } from './models';
import AcknowledgeForm from './forms/Acknowledge';

interface Props {
  disabled: boolean;
  resourcesToAcknowledge: Array<Resource>;
  onPrepareToAcknowledge;
  onCancelAcknowledge;
  onSuccess;
}

const Actions = ({
  disabled,
  resourcesToAcknowledge,
  onPrepareToAcknowledge,
  onCancelAcknowledge,
  onSuccess,
}: Props): JSX.Element => {
  return (
    <>
      <Button
        variant="contained"
        color="primary"
        disabled={disabled}
        startIcon={<IconAcknowledge />}
        onClick={onPrepareToAcknowledge}
      >
        {labelAcknowledge}
      </Button>
      <AcknowledgeForm
        resources={resourcesToAcknowledge}
        onClose={onCancelAcknowledge}
        onSuccess={onSuccess}
      />
    </>
  );
};

export default Actions;
