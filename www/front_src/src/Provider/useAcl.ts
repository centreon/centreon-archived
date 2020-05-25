import * as React from 'react';
import { defaultAcl } from './UserContext';
import { Actions } from './models';

interface AclState {
  actionAcl: Actions;
  setActionAcl: React.Dispatch<React.SetStateAction<Actions>>;
}

const useAcl = (): AclState => {
  const [actionAcl, setActionAcl] = React.useState<Actions>(defaultAcl.actions);

  return { actionAcl, setActionAcl };
};

export default useAcl;
