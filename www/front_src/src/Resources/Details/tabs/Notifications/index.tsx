import * as React from 'react';

import { isNil } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { detailsAtom } from '../../detailsAtoms';

import ContactsLoadingSkeleton from './ContactsLoadingSkeleton';
import Notification from './Notification';

const NotificationTab = (): JSX.Element => {
  const details = useAtomValue(detailsAtom);

  const loading = isNil(details);

  if (loading) {
    return <ContactsLoadingSkeleton />;
  }

  return <Notification />;
};

export default NotificationTab;
