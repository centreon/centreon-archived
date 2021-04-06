import * as React from 'react';

import { defaultUser } from './UserContext';
import { User } from './models';

interface UserState {
  setUser: React.Dispatch<React.SetStateAction<User>>;
  user: User;
}

const useUser = (): UserState => {
  const [user, setUser] = React.useState<User>(defaultUser);

  return { setUser, user };
};

export default useUser;
