import * as React from 'react';

import { Resource } from '../models';

type SetResourcesDispatch = React.Dispatch<
  React.SetStateAction<Array<Resource>>
>;

export interface ActionsState {
  resourcesToAcknowledge: Array<Resource>;
  resourcesToCheck: Array<Resource>;
  resourcesToDisacknowledge: Array<Resource>;
  resourcesToSetDowntime: Array<Resource>;
  selectedResources: Array<Resource>;
  setResourcesToAcknowledge: SetResourcesDispatch;
  setResourcesToCheck: SetResourcesDispatch;
  setResourcesToDisacknowledge: SetResourcesDispatch;
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
  const [resourcesToDisacknowledge, setResourcesToDisacknowledge] =
    React.useState<Array<Resource>>([]);

  return {
    resourcesToAcknowledge,
    resourcesToCheck,
    resourcesToDisacknowledge,
    resourcesToSetDowntime,
    selectedResources,
    setResourcesToAcknowledge,
    setResourcesToCheck,
    setResourcesToDisacknowledge,
    setResourcesToSetDowntime,
    setSelectedResources,
  };
};

export default useActions;
