import * as React from 'react';

import { all, head, pathEq, pick } from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';
import IconCheck from '@material-ui/icons/Sync';
import IconMore from '@material-ui/icons/MoreHoriz';

import {
  useCancelTokenSource,
  Severity,
  useSnackbar,
  SeverityCode,
  PopoverMenu,
} from '@centreon/ui';

import IconDowntime from '../../icons/Downtime';
import {
  labelAcknowledge,
  labelSetDowntime,
  labelCheck,
  labelSomethingWentWrong,
  labelCheckCommandSent,
  labelDisacknowledge,
  labelSubmitStatus,
  labelAddComment,
  labelMoreActions,
} from '../../translatedLabels';
import { ResourceContext, useResourceContext } from '../../Context';
import { checkResources } from '../api';
import { Resource } from '../../models';
import AddCommentForm from '../../Graph/Performance/Graph/AddCommentForm';
import memoizeComponent from '../../memoizedComponent';

import useAclQuery from './aclQuery';
import DowntimeForm from './Downtime';
import AcknowledgeForm from './Acknowledge';
import DisacknowledgeForm from './Disacknowledge';
import SubmitStatusForm from './SubmitStatus';
import ResourceActionButton from './ResourceActionButton';
import ActionMenuItem from './ActionMenuItem';

const useStyles = makeStyles((theme) => ({
  action: {
    marginRight: theme.spacing(1),
  },
  flex: {
    alignItems: 'center',
    display: 'flex',
  },
}));

type Props = Pick<
  ResourceContext,
  | 'resourcesToCheck'
  | 'selectedResources'
  | 'resourcesToAcknowledge'
  | 'resourcesToSetDowntime'
  | 'resourcesToDisacknowledge'
  | 'setSelectedResources'
  | 'setResourcesToAcknowledge'
  | 'setResourcesToSetDowntime'
  | 'setResourcesToCheck'
  | 'setResourcesToDisacknowledge'
>;

const ResourceActionsContent = ({
  resourcesToCheck,
  selectedResources,
  resourcesToAcknowledge,
  resourcesToSetDowntime,
  resourcesToDisacknowledge,
  setSelectedResources,
  setResourcesToAcknowledge,
  setResourcesToSetDowntime,
  setResourcesToCheck,
  setResourcesToDisacknowledge,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();
  const { cancel, token } = useCancelTokenSource();
  const { showMessage } = useSnackbar();

  const [resourceToSubmitStatus, setResourceToSubmitStatus] =
    React.useState<Resource | null>();
  const [resourceToComment, setResourceToComment] =
    React.useState<Resource | null>();

  const showError = (message): void =>
    showMessage({ message, severity: Severity.error });
  const showSuccess = (message): void =>
    showMessage({ message, severity: Severity.success });

  const {
    canAcknowledge,
    canDowntime,
    canCheck,
    canDisacknowledge,
    canSubmitStatus,
    canComment,
  } = useAclQuery();

  const hasResourcesToCheck = resourcesToCheck.length > 0;

  const confirmAction = (): void => {
    setSelectedResources([]);
    setResourcesToAcknowledge([]);
    setResourcesToSetDowntime([]);
    setResourcesToCheck([]);
    setResourceToSubmitStatus(null);
    setResourcesToDisacknowledge([]);
    setResourceToComment(null);
  };

  React.useEffect(() => {
    if (!hasResourcesToCheck) {
      return;
    }

    checkResources({
      cancelToken: token,
      resources: resourcesToCheck,
    })
      .then(() => {
        confirmAction();
        showSuccess(t(labelCheckCommandSent));
      })
      .catch(() => showError(t(labelSomethingWentWrong)));
  }, [resourcesToCheck]);

  React.useEffect(() => (): void => cancel(), []);

  const prepareToAcknowledge = (): void => {
    setResourcesToAcknowledge(selectedResources);
  };

  const prepareToSetDowntime = (): void => {
    setResourcesToSetDowntime(selectedResources);
  };

  const prepareToCheck = (): void => {
    setResourcesToCheck(selectedResources);
  };

  const cancelAcknowledge = (): void => {
    setResourcesToAcknowledge([]);
  };

  const cancelSetDowntime = (): void => {
    setResourcesToSetDowntime([]);
  };

  const prepareToDisacknowledge = (): void => {
    setResourcesToDisacknowledge(selectedResources);
  };

  const cancelDisacknowledge = (): void => {
    setResourcesToDisacknowledge([]);
  };

  const prepareToSubmitStatus = (): void => {
    const [selectedResource] = selectedResources;

    setResourceToSubmitStatus(selectedResource);
  };

  const cancelSubmitStatus = (): void => {
    setResourceToSubmitStatus(null);
  };

  const prepareToAddComment = (): void => {
    const [selectedResource] = selectedResources;

    setResourceToComment(selectedResource);
  };

  const cancelComment = (): void => {
    setResourceToComment(null);
  };

  const areSelectedResourcesOk = all(
    pathEq(['status', 'severity_code'], SeverityCode.Ok),
    selectedResources,
  );

  const disableAcknowledge =
    !canAcknowledge(selectedResources) || areSelectedResourcesOk;
  const disableDowntime = !canDowntime(selectedResources);
  const disableCheck = !canCheck(selectedResources);
  const disableDisacknowledge = !canDisacknowledge(selectedResources);

  const hasSelectedResources = selectedResources.length > 0;
  const hasOneResourceSelected = selectedResources.length === 1;

  const disableSubmitStatus =
    !hasOneResourceSelected ||
    !canSubmitStatus(selectedResources) ||
    !head(selectedResources)?.passive_checks;

  const disableAddComment =
    !hasOneResourceSelected || !canComment(selectedResources);

  const isAcknowledgePermitted =
    canAcknowledge(selectedResources) || !hasSelectedResources;
  const isDowntimePermitted =
    canDowntime(selectedResources) || !hasSelectedResources;
  const isCheckPermitted = canCheck(selectedResources) || !hasSelectedResources;
  const isDisacknowledgePermitted =
    canDisacknowledge(selectedResources) || !hasSelectedResources;
  const isSubmitStatusPermitted =
    canSubmitStatus(selectedResources) || !hasSelectedResources;
  const isAddCommentPermitted =
    canComment(selectedResources) || !hasSelectedResources;

  return (
    <div className={classes.flex}>
      <div className={classes.flex}>
        <div className={classes.action}>
          <ResourceActionButton
            disabled={disableAcknowledge}
            icon={<IconAcknowledge />}
            label={t(labelAcknowledge)}
            permitted={isAcknowledgePermitted}
            onClick={prepareToAcknowledge}
          />
        </div>
        <div className={classes.action}>
          <ResourceActionButton
            disabled={disableDowntime}
            icon={<IconDowntime />}
            label={t(labelSetDowntime)}
            permitted={isDowntimePermitted}
            onClick={prepareToSetDowntime}
          />
        </div>
        <div className={classes.action}>
          <ResourceActionButton
            disabled={disableCheck}
            icon={<IconCheck />}
            label={t(labelCheck)}
            permitted={isCheckPermitted}
            onClick={prepareToCheck}
          />
        </div>
        {resourcesToAcknowledge.length > 0 && (
          <AcknowledgeForm
            resources={resourcesToAcknowledge}
            onClose={cancelAcknowledge}
            onSuccess={confirmAction}
          />
        )}
        {resourcesToSetDowntime.length > 0 && (
          <DowntimeForm
            resources={resourcesToSetDowntime}
            onClose={cancelSetDowntime}
            onSuccess={confirmAction}
          />
        )}
        {resourcesToDisacknowledge.length > 0 && (
          <DisacknowledgeForm
            resources={resourcesToDisacknowledge}
            onClose={cancelDisacknowledge}
            onSuccess={confirmAction}
          />
        )}
        {resourceToSubmitStatus && (
          <SubmitStatusForm
            resource={resourceToSubmitStatus}
            onClose={cancelSubmitStatus}
            onSuccess={confirmAction}
          />
        )}
        {resourceToComment && (
          <AddCommentForm
            date={new Date()}
            resource={resourceToComment as Resource}
            onClose={cancelComment}
            onSuccess={confirmAction}
          />
        )}
      </div>

      <PopoverMenu
        icon={<IconMore color="primary" fontSize="small" />}
        title={t(labelMoreActions) as string}
      >
        {({ close }) => (
          <>
            <ActionMenuItem
              disabled={disableDisacknowledge}
              label={labelDisacknowledge}
              permitted={isDisacknowledgePermitted}
              onClick={() => {
                close();
                prepareToDisacknowledge();
              }}
            />
            <ActionMenuItem
              disabled={disableSubmitStatus}
              label={labelSubmitStatus}
              permitted={isSubmitStatusPermitted}
              onClick={() => {
                close();
                prepareToSubmitStatus();
              }}
            />

            <ActionMenuItem
              disabled={disableAddComment}
              label={labelAddComment}
              permitted={isAddCommentPermitted}
              onClick={() => {
                close();
                prepareToAddComment();
              }}
            />
          </>
        )}
      </PopoverMenu>
    </div>
  );
};

const memoProps = [
  'resourcesToCheck',
  'selectedResources',
  'resourcesToAcknowledge',
  'resourcesToSetDowntime',
  'resourcesToDisacknowledge',
];

const MemoizedResourceActionsContent = memoizeComponent<Props>({
  Component: ResourceActionsContent,
  memoProps,
});

const functionProps = [
  'setSelectedResources',
  'setResourcesToAcknowledge',
  'setResourcesToSetDowntime',
  'setResourcesToCheck',
  'setResourcesToDisacknowledge',
];

const ResourceActions = (): JSX.Element => {
  const resourceContextProps = pick(
    [...memoProps, ...functionProps],
    useResourceContext(),
  );

  return <MemoizedResourceActionsContent {...resourceContextProps} />;
};

export default ResourceActions;
