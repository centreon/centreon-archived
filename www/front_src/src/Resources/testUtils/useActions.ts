import { Dispatch, SetStateAction } from 'react';

import { useAtom } from 'jotai';

import { Resource } from '../models';
import { selectedResourcesAtom } from '../Actions/actionsAtoms';

type SetResourcesDispatch = Dispatch<SetStateAction<Array<Resource>>>;

export interface ActionsState {
  selectedResources: Array<Resource>;
  setSelectedResources: SetResourcesDispatch;
}

const useActions = (): ActionsState => {
  const [selectedResources, setSelectedResources] = useAtom(
    selectedResourcesAtom
  );

  return {
    selectedResources,
    setSelectedResources
  };
};

export default useActions;
