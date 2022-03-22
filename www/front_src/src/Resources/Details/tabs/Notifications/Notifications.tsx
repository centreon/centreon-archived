import * as React from 'react';

import { isNil, path } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { getData, useRequest } from '@centreon/ui';

import { detailsAtom } from '../../detailsAtoms';

import { Contact, ContactGroup } from './models';

interface ContactNotif {
  contact: Array<Contact> | null;
  contactGroup: Array<ContactGroup> | null;
}

const Notification = (): JSX.Element => {
  const [contact, setContact] = React.useState<Array<Contact> | null>(null);
  const [contactGroup, setContactGroup] =
    React.useState<Array<ContactGroup> | null>(null);

  const { sendRequest } = useRequest<ContactNotif>({
    request: getData,
  });
  const details = useAtomValue(detailsAtom);

  const endpoint = path(['links', 'endpoints', 'notification_policy'], details);

  const loadContactNotifs = (): void => {
    sendRequest({ endpoint: `./api/${endpoint}` })
      .then(({ contact, contactGroup }) => {
        setContact(contact);
        setContactGroup(contactGroup);
      })
      .catch(() => undefined);
  };

  React.useEffect(() => {
    if (!details?.notification_enabled) {
      return;
    }
    loadContactNotifs();
  }, [details?.notification_enabled]);

  return (
    <div>
      {!isNil(contact) &&
        contact?.map(({ id, name, alias, email, configuration_uri }) => (
          <div key={id}>
            <li>{name}</li>/<li>{alias}</li>
            <li>{email}</li>
            <li>{configuration_uri}</li>
          </div>
        ))}
      {!isNil(contactGroup) &&
        contactGroup?.map(({ id, name, alias, configuration_uri }) => (
          <div key={id}>
            <li>{name}</li>/<li>{alias}</li>
            <li>{configuration_uri}</li>
          </div>
        ))}
    </div>
  );
};

export default Notification;
