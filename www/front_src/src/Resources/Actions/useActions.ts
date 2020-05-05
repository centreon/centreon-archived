import * as React from 'react';

import { Resource } from '../models';

const useActions = () => {
  const [selectedResources, setSelectedResources] = React.useState<
    Array<Resource>
  >([]);
  const [resourcesToAcknowledge, setResourcesToAcknowledge] = React.useState<
    Array<Resource>
  >([]);
  const [resourcesToSetDowntime, setResourcesToSetDowntime] = React.useState<
    Array<Resource>
  >([]);
  const [resourcesToCheck, setResourcesToCheck] = React.useState<
    Array<Resource>
  >([]);

  return {
    selectedResources,
    setSelectedResources,
    resourcesToAcknowledge,
    setResourcesToAcknowledge,
    resourcesToSetDowntime,
    setResourcesToSetDowntime,
    resourcesToCheck,
    setResourcesToCheck,
  };
};

export default useActions;
