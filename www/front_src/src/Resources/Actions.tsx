import * as React from 'react';

import { Button } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';

import { labelAcknowledge } from './translatedLabels';
import { Resource } from './models';
import AcknowledgeForm from './forms/Acknowledge';

interface Props {
  selectedResources: Array<Resource>;
  onSuccess;
}

const Actions = ({ selectedResources, onSuccess }: Props): JSX.Element => {
  const [resourcesToAcknowledge, setResourcesToAcknoweledge] = React.useState<
    Array<Resource>
  >([]);

  const prepareAcknoweldge = (): void => {
    setResourcesToAcknoweledge(selectedResources);
  };

  const resetAcknowledge = (): void => {
    setResourcesToAcknoweledge([]);
  };

  const resetAcknowledgeAndSucceed = (): void => {
    resetAcknowledge();
    onSuccess();
  };

  const hasSelectedResources = selectedResources.length > 0;

  return (
    <>
      <Button
        variant="contained"
        color="primary"
        disabled={!hasSelectedResources}
        startIcon={<IconAcknowledge />}
        onClick={prepareAcknoweldge}
      >
        {labelAcknowledge}
      </Button>
      <AcknowledgeForm
        resources={resourcesToAcknowledge}
        onClose={resetAcknowledge}
        onSuccess={resetAcknowledgeAndSucceed}
      />
    </>
  );
};

export default Actions;
