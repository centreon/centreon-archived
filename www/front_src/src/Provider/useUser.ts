import * as React from 'react';

import { defaultUser } from './UserContext';
import { User } from './models';

interface UserState {
  user: User;
  setUser: React.Dispatch<React.SetStateAction<User>>;
}

const useUser = (): UserState => {
  const [user, setUser] = React.useState<User>(defaultUser);

  return { user, setUser };
};

export default useUser;
