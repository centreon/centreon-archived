import * as React from 'react';

import { Resource } from '../models';

type SetResourcesDispatch = React.Dispatch<
  React.SetStateAction<Array<Resource>>
>;

export interface ActionsState {
  resourcesToAcknowledge: Array<Resource>;
  resourcesToCheck: Array<Resource>;
  resourcesToSetDowntime: Array<Resource>;
  selectedResources: Array<Resource>;
  setResourcesToAcknowledge: SetResourcesDispatch;
  setResourcesToCheck: SetResourcesDispatch;
  setResourcesToSetDowntime: SetResourcesDispatch;
  setSelectedResources: SetResourcesDispatch;
}

const useActions = (): ActionsState => {
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
    resourcesToAcknowledge,
    resourcesToCheck,
    resourcesToSetDowntime,
    selectedResources,
    setResourcesToAcknowledge,
    setResourcesToCheck,
    setResourcesToSetDowntime,
    setSelectedResources,
  };
};

export default useActions;
