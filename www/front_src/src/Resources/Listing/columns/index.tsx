import { pipe, split, head, propOr, T } from 'ramda';

import makeStyles from '@mui/styles/makeStyles';
import { CreateCSSProperties } from '@mui/styles';
import { Theme } from '@mui/material';

import { ColumnType, Column } from '@centreon/ui';

import {
  labelResource,
  labelStatus,
  labelDuration,
  labelTries,
  labelInformation,
  labelState,
  labelLastCheck,
  labelParent,
  labelNotes,
  labelAction,
  labelGraph,
  labelAlias,
  labelFqdn,
  labelMonitoringServer,
  labelNotification,
  labelCheck,
  labelSeverity,
  labelParentAlias,
} from '../../translatedLabels';
import truncate from '../../truncate';

import StateColumn from './State';
import GraphColumn from './Graph';
import NotesUrlColumn from './Url/Notes';
import ActionUrlColumn from './Url/Action';
import StatusColumn from './Status';
import SeverityColumn from './Severity';
import ResourceColumn from './Resource';
import ParentResourceColumn from './Parent';
import NotificationColumn from './Notification';
import ChecksColumn from './Checks';
import ParentAliasColumn from './ParentAlias';

interface StyleProps {
  isHovered: boolean;
}

const useStyles = makeStyles<Theme, StyleProps>((theme) => ({
  resourceDetailsCell: {
    alignItems: 'center',
    display: 'flex',
    flexWrap: 'nowrap',
    padding: theme.spacing(0, 0.5),
  },
  resourceNameItem: {
    lineHeight: 1,
    marginLeft: theme.spacing(1),
    whiteSpace: 'nowrap',
  },
  resourceNameText: ({ isHovered }): CreateCSSProperties => ({
    color: isHovered
      ? theme.palette.text.primary
      : theme.palette.text.secondary,
    lineHeight: 1,
  }),
}));

export interface ColumnProps {
  actions;
  t: (value: string) => string;
}

export const defaultSelectedColumnIds = [
  'severity',
  'status',
  'resource',
  'parent_resource',
  'notes_url',
  'action_url',
  'graph',
  'duration',
  'tries',
  'last_check',
  'information',
  'state',
];

export const getColumns = ({ actions, t }: ColumnProps): Array<Column> => [
  {
    Component: SeverityColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'severity',
    label: t(labelSeverity),
    rowMemoProps: ['severity_level'],
    shortLabel: 'S',
    sortField: 'severity_level',
    sortable: true,
    type: ColumnType.component,
  },
  {
    Component: StatusColumn({ actions, t }),
    clickable: true,
    getRenderComponentOnRowUpdateCondition: T,
    hasHoverableComponent: true,
    id: 'status',
    label: t(labelStatus),
    rowMemoProps: ['status', 'severity_code', 'type'],
    sortField: 'status_severity_code',
    sortable: true,
    type: ColumnType.component,
    width: 'minmax(100px, max-content)',
  },
  {
    Component: ResourceColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'resource',
    label: t(labelResource),
    rowMemoProps: ['icon', 'short_type', 'name'],
    sortField: 'name',
    sortable: true,
    type: ColumnType.component,
  },
  {
    Component: ParentResourceColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'parent_resource',
    label: t(labelParent),
    rowMemoProps: ['parent'],
    sortField: 'parent_name',
    sortable: true,
    type: ColumnType.component,
  },
  {
    Component: NotesUrlColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'notes_url',
    label: t(labelNotes),
    rowMemoProps: ['links'],
    shortLabel: 'N',
    sortable: false,
    type: ColumnType.component,
  },
  {
    Component: ActionUrlColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'action_url',
    label: t(labelAction),
    rowMemoProps: ['links'],
    shortLabel: 'A',
    sortable: false,
    type: ColumnType.component,
  },
  {
    Component: GraphColumn({ onClick: actions.onDisplayGraph }),
    getRenderComponentOnRowUpdateCondition: T,
    id: 'graph',
    label: t(labelGraph),
    shortLabel: 'G',
    sortable: false,
    type: ColumnType.component,
  },
  {
    getFormattedString: ({ duration }): string => duration,
    id: 'duration',
    label: t(labelDuration),
    sortField: 'last_status_change',
    sortable: true,
    type: ColumnType.string,
  },
  {
    getFormattedString: ({ tries }): string => tries,
    id: 'tries',
    label: t(labelTries),
    sortable: true,
    type: ColumnType.string,
  },
  {
    getFormattedString: ({ last_check }): string => last_check,
    id: 'last_check',
    label: t(labelLastCheck),
    sortable: true,
    type: ColumnType.string,
  },
  {
    getFormattedString: pipe(
      propOr('', 'information'),
      split('\n'),
      head,
      truncate,
    ) as (row) => string,
    id: 'information',
    label: t(labelInformation),
    sortable: false,
    type: ColumnType.string,
    width: 'minmax(50px, 1fr)',
  },
  {
    Component: StateColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'state',
    label: t(labelState),
    rowMemoProps: ['in_downtime', 'acknowledged', 'name', 'links'],
    sortable: false,
    type: ColumnType.component,
  },
  {
    getFormattedString: ({ alias }): string => alias,
    id: 'alias',
    label: t(labelAlias),
    sortable: true,
    type: ColumnType.string,
  },
  {
    Component: ParentAliasColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'parent_alias',
    label: t(labelParentAlias),
    rowMemoProps: ['parent'],
    sortField: 'parent_alias',
    sortable: true,
    type: ColumnType.component,
  },
  {
    getFormattedString: ({ fqdn }): string => fqdn,
    id: 'fqdn',
    label: t(labelFqdn),
    sortable: true,
    type: ColumnType.string,
  },
  {
    getFormattedString: ({ monitoring_server_name }): string =>
      monitoring_server_name,
    id: 'monitoring_server_name',
    label: t(labelMonitoringServer),
    sortable: true,
    type: ColumnType.string,
  },
  {
    Component: NotificationColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'notification',
    label: t(labelNotification),
    rowMemoProps: ['notification_enabled'],
    shortLabel: 'Notif',
    type: ColumnType.component,
  },
  {
    Component: ChecksColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'checks',
    label: t(labelCheck),
    rowMemoProps: ['passive_checks', 'active_checks'],
    shortLabel: 'C',
    type: ColumnType.component,
  },
];

export { useStyles as useColumnStyles };
