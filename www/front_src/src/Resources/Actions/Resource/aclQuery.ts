import {
  pipe,
  any,
  map,
  pathEq,
  partition,
  propEq,
  find,
  head,
  ifElse,
  isNil,
  always,
  equals,
} from 'ramda';

import { Resource } from '../../models';
import { useUserContext } from '../../../Provider/UserContext';
import { labelServicesDenied, labelHostsDenied } from '../../translatedLabels';

interface AclQuery {
  canDowntime: (resources) => boolean;
  getDowntimeDeniedTypeAlert: (resources) => string | undefined;
  canAcknowledge: (resources) => boolean;
  getAcknowledgementDeniedTypeAlert: (resources) => string | undefined;
  canCheck: (resources) => boolean;
}

const useAclQuery = (): AclQuery => {
  const { acl } = useUserContext();

  const toType = ({ type }): string => type;

  const can = ({
    resources,
    action,
  }: {
    resources: Array<Resource>;
    action: string;
  }): boolean => {
    return pipe(
      map(toType),
      any((type) => pathEq(['actions', type, action], true)(acl)),
    )(resources);
  };

  const cannot = (action) => (resources): boolean =>
    !can({ resources, action });

  const getDeniedTypeAlert = ({ resources, action }): string | undefined => {
    const isHost = propEq('type', 'host');

    return pipe(
      partition(isHost),
      find(cannot(action)),
      head,
      ifElse(
        isNil,
        always(undefined),
        pipe(
          toType,
          ifElse(
            equals('service'),
            always(labelServicesDenied),
            always(labelHostsDenied),
          ),
        ),
      ),
    )(resources);
  };

  const canDowntime = (resources: Array<Resource>): boolean => {
    return can({ resources, action: 'downtime' });
  };

  const getDowntimeDeniedTypeAlert = (
    resources: Array<Resource>,
  ): string | undefined => {
    return getDeniedTypeAlert({ resources, action: 'downtime' });
  };

  const canAcknowledge = (resources: Array<Resource>): boolean => {
    return can({ resources, action: 'acknowledgement' });
  };

  const getAcknowledgementDeniedTypeAlert = (
    resources: Array<Resource>,
  ): string | undefined => {
    return getDeniedTypeAlert({ resources, action: 'acknowledgement' });
  };

  const canCheck = (resources: Array<Resource>): boolean => {
    return can({ resources, action: 'check' });
  };

  return {
    canDowntime,
    getDowntimeDeniedTypeAlert,
    canAcknowledge,
    getAcknowledgementDeniedTypeAlert,
    canCheck,
  };
};

export default useAclQuery;
