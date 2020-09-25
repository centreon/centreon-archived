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
import { useTranslation } from 'react-i18next';

import { Resource } from '../../models';
import { useUserContext } from '../../../Provider/UserContext';
import { labelServicesDenied, labelHostsDenied } from '../../translatedLabels';

interface AclQuery {
  canDowntime: (resources) => boolean;
  getDowntimeDeniedTypeAlert: (resources) => string | undefined;
  canDowntimeServices: () => boolean;
  canAcknowledge: (resources) => boolean;
  getAcknowledgementDeniedTypeAlert: (resources) => string | undefined;
  canAcknowledgeServices: () => boolean;
  canCheck: (resources) => boolean;
  canDisacknowledge: (resources) => boolean;
  canDisacknowledgeServices: () => boolean;
  getDisacknowledgementDeniedTypeAlert: (resources) => string | undefined;
  canSubmitStatus: (resource) => boolean;
}

const useAclQuery = (): AclQuery => {
  const { t } = useTranslation();
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
            always(t(labelHostsDenied)),
            always(t(labelServicesDenied)),
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

  const canDowntimeServices = (): boolean =>
    pathEq(['actions', 'service', 'downtime'], true)(acl);

  const canAcknowledge = (resources: Array<Resource>): boolean => {
    return can({ resources, action: 'acknowledgement' });
  };

  const getAcknowledgementDeniedTypeAlert = (
    resources: Array<Resource>,
  ): string | undefined => {
    return getDeniedTypeAlert({ resources, action: 'acknowledgement' });
  };

  const canAcknowledgeServices = (): boolean =>
    pathEq(['actions', 'service', 'acknowledgement'], true)(acl);

  const canCheck = (resources: Array<Resource>): boolean => {
    return can({ resources, action: 'check' });
  };

  const canDisacknowledge = (resources: Array<Resource>): boolean => {
    return can({ resources, action: 'disacknowledgement' });
  };

  const canDisacknowledgeServices = (): boolean =>
    pathEq(['actions', 'service', 'disacknowledgement'], true)(acl);

  const getDisacknowledgementDeniedTypeAlert = (
    resources: Array<Resource>,
  ): string | undefined => {
    return getDeniedTypeAlert({ resources, action: 'disacknowledgement' });
  };

  const canSubmitStatus = (resources: Array<Resource>): boolean => {
    return can({ resources, action: 'submit_status' });
  };

  return {
    canDowntime,
    getDowntimeDeniedTypeAlert,
    canDowntimeServices,
    canAcknowledge,
    getAcknowledgementDeniedTypeAlert,
    canAcknowledgeServices,
    canCheck,
    canDisacknowledge,
    canDisacknowledgeServices,
    getDisacknowledgementDeniedTypeAlert,
    canSubmitStatus,
  };
};

export default useAclQuery;
