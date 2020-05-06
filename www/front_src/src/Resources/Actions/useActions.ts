import * as React from 'react';

import { Resource } from '../models';

type SetResourcesDispatch = React.Dispatch<
  React.SetStateAction<Array<Resource>>
>;

export interface ActionsState {
  selectedResources: Array<Resource>;
  setSelectedResources: SetResourcesDispatch;
  resourcesToAcknowledge: Array<Resource>;
  setResourcesToAcknowledge: SetResourcesDispatch;
  resourcesToSetDowntime: Array<Resource>;
  setResourcesToSetDowntime: SetResourcesDispatch;
  resourcesToCheck: Array<Resource>;
  setResourcesToCheck: SetResourcesDispatch;
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
