import { FC } from 'react';

import { useAtom } from 'jotai';

import { userAtom } from '@centreon/ui-context';

export const enhancedComponent = (
  WrappedComponent: (props) => JSX.Element,
): FC => {
  return () => {
    const [user, setUser] = useAtom(userAtom);
    const props = { setUser, user };

    return <WrappedComponent {...props} />;
  };
};
