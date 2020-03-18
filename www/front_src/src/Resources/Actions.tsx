import * as React from 'react';

import { Button } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';

import { labelAcknowledge } from './translatedLabels';
import { Resource } from './models';
import AcknowledgeForm from './forms/Acknowledge';

interface Props {
  selectedResources: Array<Resource>;
}

const Actions = ({ selectedResources }: Props): JSX.Element => {
  const [resourcesToAcknowledge, setResourcesToAcknoweledge] = React.useState<
    Array<Resource>
  >([]);

  const prepareAcknoweldge = (): void => {
    setResourcesToAcknoweledge(selectedResources);
  };

  const resetAcknowledge = (): void => {
    setResourcesToAcknoweledge([]);
  };

  const confirmAcknowledge = (): void => {
    // TODO;
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
      />
    </>
  );
};

export default Actions;
