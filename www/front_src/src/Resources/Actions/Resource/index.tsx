import * as React from 'react';

import { all, head, pathEq, pick } from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles, Menu, MenuItem } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';
import IconCheck from '@material-ui/icons/Sync';
import IconMore from '@material-ui/icons/MoreHoriz';

import {
  useCancelTokenSource,
  Severity,
  useSnackbar,
  SeverityCode,
  IconButton,
} from '@centreon/ui';

import IconDowntime from '../../icons/Downtime';
import {
  labelAcknowledge,
  labelSetDowntime,
  labelCheck,
  labelSomethingWentWrong,
  labelCheckCommandSent,
  labelMoreActions,
  labelDisacknowledge,
  labelSubmitStatus,
  labelAddComment,
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
  const [moreActionsMenuAnchor, setMoreActionsMenuAnchor] =
    React.useState<Element | null>(null);

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

  const closeMoreActionsMenu = (): void => {
    setMoreActionsMenuAnchor(null);
  };

  const prepareToDisacknowledge = (): void => {
    closeMoreActionsMenu();
    setResourcesToDisacknowledge(selectedResources);
  };

  const cancelDisacknowledge = (): void => {
    setResourcesToDisacknowledge([]);
  };

  const prepareToSubmitStatus = (): void => {
    closeMoreActionsMenu();
    const [selectedResource] = selectedResources;

    setResourceToSubmitStatus(selectedResource);
  };

  const cancelSubmitStatus = (): void => {
    setResourceToSubmitStatus(null);
  };

  const prepareToAddComment = (): void => {
    closeMoreActionsMenu();
    const [selectedResource] = selectedResources;

    setResourceToComment(selectedResource);
  };

  const cancelComment = (): void => {
    setResourceToComment(null);
  };

  const openMoreActionsMenu = (event: React.MouseEvent): void => {
    setMoreActionsMenuAnchor(event.currentTarget);
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

  const disableSubmitStatus =
    selectedResources.length !== 1 ||
    !canSubmitStatus(selectedResources) ||
    !head(selectedResources)?.passive_checks;

  const disableAddComment =
    selectedResources.length !== 1 || !canComment(selectedResources);

  return (
    <div className={classes.flex}>
      <div className={classes.flex}>
        <div className={classes.action}>
          <ResourceActionButton
            disabled={disableAcknowledge}
            icon={<IconAcknowledge />}
            label={t(labelAcknowledge)}
            onClick={prepareToAcknowledge}
          />
        </div>
        <div className={classes.action}>
          <ResourceActionButton
            disabled={disableDowntime}
            icon={<IconDowntime />}
            label={t(labelSetDowntime)}
            onClick={prepareToSetDowntime}
          />
        </div>
        <div className={classes.action}>
          <ResourceActionButton
            disabled={disableCheck}
            icon={<IconCheck />}
            label={t(labelCheck)}
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

      <div className={classes.flex}>
        <IconButton title={t(labelMoreActions)} onClick={openMoreActionsMenu}>
          <IconMore color="primary" fontSize="small" />
        </IconButton>
      </div>

      <Menu
        keepMounted
        anchorEl={moreActionsMenuAnchor}
        open={Boolean(moreActionsMenuAnchor)}
        onClose={closeMoreActionsMenu}
      >
        <MenuItem
          disabled={disableDisacknowledge}
          onClick={prepareToDisacknowledge}
        >
          {t(labelDisacknowledge)}
        </MenuItem>
        <MenuItem
          disabled={disableSubmitStatus}
          onClick={prepareToSubmitStatus}
        >
          {t(labelSubmitStatus)}
        </MenuItem>
        <MenuItem disabled={disableAddComment} onClick={prepareToAddComment}>
          {t(labelAddComment)}
        </MenuItem>
      </Menu>
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
