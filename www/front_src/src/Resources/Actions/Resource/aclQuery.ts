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
  isEmpty,
  reject,
} from 'ramda';

import { Resource } from '../../models';
import { useUserContext } from '../../../Provider/UserContext';
import { labelServicesDenied, labelHostsDenied } from '../../translatedLabels';

interface AclQuery {
  canAcknowledge: (resources) => boolean;
  canAcknowledgeServices: () => boolean;
  canCheck: (resources) => boolean;
  canDowntime: (resources) => boolean;
  canDowntimeServices: () => boolean;
  getAcknowledgementDeniedTypeAlert: (resources) => string | undefined;
  getDowntimeDeniedTypeAlert: (resources) => string | undefined;
}

const useAclQuery = (): AclQuery => {
  const { acl } = useUserContext();

  const toType = ({ type }): string => type;

  const can = ({
    resources,
    action,
  }: {
    action: string;
    resources: Array<Resource>;
  }): boolean => {
    return pipe(
      map(toType),
      any((type) => pathEq(['actions', type, action], true)(acl)),
    )(resources);
  };

  const cannot =
    (action) =>
    (resources): boolean =>
      !can({ action, resources });

  const getDeniedTypeAlert = ({ resources, action }): string | undefined => {
    const isHost = propEq('type', 'host');

    return pipe(
      partition(isHost),
      reject(isEmpty),
      find(cannot(action)),
      ifElse(
        isNil,
        always(undefined),
        pipe(
          head,
          toType,
          ifElse(
            equals('host'),
            always(labelHostsDenied),
            always(labelServicesDenied),
          ),
        ),
      ),
    )(resources);
  };

  const canDowntime = (resources: Array<Resource>): boolean => {
    return can({ action: 'downtime', resources });
  };

  const getDowntimeDeniedTypeAlert = (
    resources: Array<Resource>,
  ): string | undefined => {
    return getDeniedTypeAlert({ action: 'downtime', resources });
  };

  const canDowntimeServices = (): boolean =>
    pathEq(['actions', 'service', 'downtime'], true)(acl);

  const canAcknowledge = (resources: Array<Resource>): boolean => {
    return can({ action: 'acknowledgement', resources });
  };

  const getAcknowledgementDeniedTypeAlert = (
    resources: Array<Resource>,
  ): string | undefined => {
    return getDeniedTypeAlert({ action: 'acknowledgement', resources });
  };

  const canAcknowledgeServices = (): boolean =>
    pathEq(['actions', 'service', 'acknowledgement'], true)(acl);

  const canCheck = (resources: Array<Resource>): boolean => {
    return can({ action: 'check', resources });
  };

  return {
    canAcknowledge,
    canAcknowledgeServices,
    canCheck,
    canDowntime,
    canDowntimeServices,
    getAcknowledgementDeniedTypeAlert,
    getDowntimeDeniedTypeAlert,
  };
};

export default useAclQuery;
