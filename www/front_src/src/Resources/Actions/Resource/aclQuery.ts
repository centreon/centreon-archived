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
  reject
} from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';

import { aclAtom } from '@centreon/ui-context';

import { Resource, ResourceCategory } from '../../models';
import { labelServicesDenied, labelHostsDenied } from '../../translatedLabels';

interface AclQuery {
  canAcknowledge: (resources) => boolean;
  canAcknowledgeServices: () => boolean;
  canCheck: (resources) => boolean;
  canComment: (resources) => boolean;
  canDisacknowledge: (resources) => boolean;
  canDisacknowledgeServices: () => boolean;
  canDowntime: (resources) => boolean;
  canDowntimeServices: () => boolean;
  canSubmitStatus: (resources) => boolean;
  getAcknowledgementDeniedTypeAlert: (resources) => string | undefined;
  getDisacknowledgementDeniedTypeAlert: (resources) => string | undefined;
  getDowntimeDeniedTypeAlert: (resources) => string | undefined;
}

const useAclQuery = (): AclQuery => {
  const { t } = useTranslation();
  const acl = useAtomValue(aclAtom);

  const toType = ({ type }): string => ResourceCategory[type];

  const can = ({
    resources,
    action
  }: {
    action: string;
    resources: Array<Resource>;
  }): boolean => {
    return pipe(
      map(toType),
      any((type) => pathEq(['actions', type, action], true)(acl))
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
            always(t(labelHostsDenied)),
            always(t(labelServicesDenied))
          )
        )
      )
    )(resources);
  };

  const canDowntime = (resources: Array<Resource>): boolean => {
    return can({ action: 'downtime', resources });
  };

  const getDowntimeDeniedTypeAlert = (
    resources: Array<Resource>
  ): string | undefined => {
    return getDeniedTypeAlert({ action: 'downtime', resources });
  };

  const canDowntimeServices = (): boolean =>
    pathEq(['actions', 'service', 'downtime'], true)(acl);

  const canAcknowledge = (resources: Array<Resource>): boolean => {
    return can({ action: 'acknowledgement', resources });
  };

  const getAcknowledgementDeniedTypeAlert = (
    resources: Array<Resource>
  ): string | undefined => {
    return getDeniedTypeAlert({ action: 'acknowledgement', resources });
  };

  const canAcknowledgeServices = (): boolean =>
    pathEq(['actions', 'service', 'acknowledgement'], true)(acl);

  const canCheck = (resources: Array<Resource>): boolean => {
    return can({ action: 'check', resources });
  };

  const canDisacknowledge = (resources: Array<Resource>): boolean => {
    return can({ action: 'disacknowledgement', resources });
  };

  const canDisacknowledgeServices = (): boolean =>
    pathEq(['actions', 'service', 'disacknowledgement'], true)(acl);

  const getDisacknowledgementDeniedTypeAlert = (
    resources: Array<Resource>
  ): string | undefined => {
    return getDeniedTypeAlert({ action: 'disacknowledgement', resources });
  };

  const canSubmitStatus = (resources: Array<Resource>): boolean => {
    return can({ action: 'submit_status', resources });
  };

  const canComment = (resources: Array<Resource>): boolean => {
    return can({ action: 'comment', resources });
  };

  return {
    canAcknowledge,
    canAcknowledgeServices,
    canCheck,
    canComment,
    canDisacknowledge,
    canDisacknowledgeServices,
    canDowntime,
    canDowntimeServices,
    canSubmitStatus,
    getAcknowledgementDeniedTypeAlert,
    getDisacknowledgementDeniedTypeAlert,
    getDowntimeDeniedTypeAlert
  };
};

export default useAclQuery;
