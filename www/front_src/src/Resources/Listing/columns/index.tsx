import { pipe, split, head, propOr, T } from 'ramda';

import { makeStyles } from '@material-ui/core';

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

const useStyles = makeStyles((theme) => ({
  resourceDetailsCell: {
    padding: theme.spacing(0, 0.5),
    display: 'flex',
    flexWrap: 'nowrap',
    alignItems: 'center',
  },
  resourceNameItem: {
    marginLeft: theme.spacing(1),
    whiteSpace: 'nowrap',
  },
  extraSmallChipContainer: {
    height: 19,
  },
  smallChipContainer: {
    height: theme.spacing(2.5),
    width: theme.spacing(2.5),
    fontSize: 10,
  },
  smallChipLabel: {
    padding: theme.spacing(0.5),
  },
  actions: {
    display: 'flex',
    flexWrap: 'nowrap',
    gridGap: theme.spacing(0.75),
    alignItems: 'center',
    justifyContent: 'center',
  },
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
    id: 'severity',
    label: 'S',
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: SeverityColumn,
    sortField: 'severity_level',
    sortable: true,
  },
  {
    id: 'status',
    label: t(labelStatus),
    type: ColumnType.component,
    Component: StatusColumn({ actions, t }),
    hasHoverableComponent: true,
    getRenderComponentOnRowUpdateCondition: T,
    sortField: 'status_severity_code',
    clickable: true,
    width: 'minmax(100px, max-content)',
    sortable: true,
  },
  {
    id: 'resource',
    label: t(labelResource),
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: ResourceColumn,
    sortField: 'name',
    sortable: true,
  },
  {
    id: 'parent_resource',
    label: t(labelParent),
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: ParentResourceColumn,
    sortField: 'parent_name',
    sortable: true,
  },
  {
    id: 'notes_url',
    label: t(labelNotes),
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: NotesUrlColumn,
    sortable: false,
  },
  {
    id: 'action_url',
    label: t(labelAction),
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: ActionUrlColumn,
    sortable: false,
  },
  {
    id: 'graph',
    label: t(labelGraph),
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: GraphColumn({ onClick: actions.onDisplayGraph }),
    sortable: false,
  },
  {
    id: 'duration',
    label: t(labelDuration),
    type: ColumnType.string,
    getFormattedString: ({ duration }): string => duration,
    sortField: 'last_status_change',
    sortable: true,
  },
  {
    id: 'tries',
    label: t(labelTries),
    type: ColumnType.string,
    getFormattedString: ({ tries }): string => tries,
    sortable: true,
  },
  {
    id: 'last_check',
    label: t(labelLastCheck),
    type: ColumnType.string,
    getFormattedString: ({ last_check }): string => last_check,
    sortable: true,
  },
  {
    id: 'information',
    label: t(labelInformation),
    type: ColumnType.string,
    sortable: false,
    width: '1fr',
    getFormattedString: pipe(
      propOr('', 'information'),
      split('\n'),
      head,
      truncate,
    ) as (row) => string,
  },
  {
    id: 'state',
    label: t(labelState),
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: StateColumn,
    sortable: false,
  },
  {
    id: 'alias',
    label: t(labelAlias),
    type: ColumnType.string,
    getFormattedString: ({ alias }): string => alias,
    sortable: true,
  },
  {
    id: 'fqdn',
    label: t(labelFqdn),
    type: ColumnType.string,
    getFormattedString: ({ fqdn }): string => fqdn,
    sortable: true,
  },
  {
    id: 'monitoring_server_name',
    label: t(labelMonitoringServer),
    sortable: true,
    type: ColumnType.string,
    getFormattedString: ({ monitoring_server_name }): string =>
      monitoring_server_name,
  },
  {
    id: 'notification',
    label: t(labelNotification),
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: NotificationColumn,
  },
  {
    id: 'checks',
    label: t(labelCheck),
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: ChecksColumn,
  },
];

export { useStyles as useColumnStyles };
