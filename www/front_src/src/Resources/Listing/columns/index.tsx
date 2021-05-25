import React from 'react';

import { pipe, split, head, propOr, T } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Grid, Typography, makeStyles } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';
import IconCheck from '@material-ui/icons/Sync';

import {
  ColumnType,
  StatusChip,
  SeverityCode,
  IconButton,
  Column,
  ComponentColumnProps,
} from '@centreon/ui';

import IconDowntime from '../../icons/Downtime';
import {
  labelResource,
  labelStatus,
  labelDuration,
  labelTries,
  labelInformation,
  labelState,
  labelLastCheck,
  labelAcknowledge,
  labelSetDowntimeOn,
  labelCheck,
  labelSetDowntime,
  labelParent,
} from '../../translatedLabels';
import useAclQuery from '../../Actions/Resource/aclQuery';
import truncate from '../../truncate';

import StateColumn from './State';
import GraphColumn from './Graph';
import UrlColumn from './Url';

const useStyles = makeStyles((theme) => ({
  extraSmallChipContainer: {
    height: 19,
  },
  resourceDetailsCell: {
    alignItems: 'center',
    display: 'flex',
    flexWrap: 'nowrap',
    padding: theme.spacing(0, 0.5),
  },
  resourceNameItem: {
    marginLeft: theme.spacing(1),
    whiteSpace: 'nowrap',
  },
  smallChipContainer: {
    fontSize: 10,
    height: theme.spacing(2.5),
    width: theme.spacing(2.5),
  },
  smallChipLabel: {
    padding: theme.spacing(0.5),
  },
}));

const SeverityColumn = ({ row }: ComponentColumnProps): JSX.Element | null => {
  const classes = useStyles();

  if (!row.severity_level) {
    return null;
  }

  return (
    <StatusChip
      classes={{
        label: classes.smallChipLabel,
        root: classes.extraSmallChipContainer,
      }}
      label={row.severity_level?.toString()}
      severityCode={SeverityCode.None}
    />
  );
};

type StatusColumnProps = {
  actions;
} & Pick<ComponentColumnProps, 'row'>;

const StatusColumnOnHover = ({
  actions,
  row,
}: StatusColumnProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { canAcknowledge, canDowntime, canCheck } = useAclQuery();

  const disableAcknowledge = !canAcknowledge([row]);
  const disableDowntime = !canDowntime([row]);
  const disableCheck = !canCheck([row]);

  return (
    <Grid container alignItems="center" spacing={1}>
      <Grid item>
        <IconButton
          ariaLabel={`${t(labelAcknowledge)} ${row.name}`}
          color="primary"
          disabled={disableAcknowledge}
          title={t(labelAcknowledge)}
          onClick={(): void => actions.onAcknowledge(row)}
        >
          <IconAcknowledge fontSize="small" />
        </IconButton>
      </Grid>
      <Grid item>
        <IconButton
          ariaLabel={`${t(labelSetDowntimeOn)} ${row.name}`}
          disabled={disableDowntime}
          title={t(labelSetDowntime)}
          onClick={(): void => actions.onDowntime(row)}
        >
          <IconDowntime fontSize="small" />
        </IconButton>
      </Grid>
      <Grid item>
        <IconButton
          ariaLabel={`${t(labelCheck)} ${row.name}`}
          disabled={disableCheck}
          title={t(labelCheck)}
          onClick={(): void => actions.onCheck(row)}
        >
          <IconCheck fontSize="small" />
        </IconButton>
      </Grid>
      <Grid item>
        <StatusChip
          classes={{
            label: classes.smallChipLabel,
            root: classes.smallChipContainer,
          }}
          label={row.status.name[0]}
          severityCode={row.status.severity_code}
        />
      </Grid>
    </Grid>
  );
};

const StatusColumn =
  ({ actions, t }) =>
  ({ row, isHovered }: ComponentColumnProps): JSX.Element => {
    return isHovered ? (
      <StatusColumnOnHover actions={actions} row={row} />
    ) : (
      <StatusChip
        label={t(row.status.name)}
        severityCode={row.status.severity_code}
        style={{ height: 20, margin: 2, width: 100 }}
      />
    );
  };

const ResourceColumn = ({ row }: ComponentColumnProps): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.resourceDetailsCell}>
      {row.icon ? (
        <img alt={row.icon.name} height={16} src={row.icon.url} width={16} />
      ) : (
        <StatusChip
          classes={{
            label: classes.smallChipLabel,
            root: classes.extraSmallChipContainer,
          }}
          label={row.short_type}
          severityCode={SeverityCode.None}
        />
      )}
      <div className={classes.resourceNameItem}>
        <Typography variant="body2">{row.name}</Typography>
      </div>
    </div>
  );
};

const ParentResourceColumn = ({
  row,
}: ComponentColumnProps): JSX.Element | null => {
  const classes = useStyles();

  if (!row.parent) {
    return null;
  }

  return (
    <div className={classes.resourceDetailsCell}>
      <StatusChip severityCode={row.parent?.status?.severity_code || 0} />
      <div className={classes.resourceNameItem}>
        <Typography variant="body2">{row.parent.name}</Typography>
      </div>
    </div>
  );
};

interface ColumnsProps {
  actions;
  t: (value: string) => string;
}

export const getColumns = ({ actions, t }: ColumnsProps): Array<Column> => [
  {
    Component: SeverityColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'severity',
    label: 'S',
    sortField: 'severity_level',
    type: ColumnType.component,
    width: 50,
  },
  {
    Component: StatusColumn({ actions, t }),
    clickable: true,
    getRenderComponentOnRowUpdateCondition: T,
    hasHoverableComponent: true,
    id: 'status',
    label: t(labelStatus),
    sortField: 'status_severity_code',
    type: ColumnType.component,
    width: 145,
  },
  {
    Component: ResourceColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'resource',
    label: t(labelResource),
    sortField: 'name',
    type: ColumnType.component,
    width: 200,
  },
  {
    Component: ParentResourceColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'parent_name',
    label: t(labelParent),
    type: ColumnType.component,
    width: 200,
  },
  {
    Component: UrlColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'url',
    label: '',
    sortable: false,
    type: ColumnType.component,
    width: 50,
  },
  {
    Component: GraphColumn({ onClick: actions.onDisplayGraph }),
    getRenderComponentOnRowUpdateCondition: T,
    id: 'graph',
    label: '',
    sortable: false,
    type: ColumnType.component,
    width: 50,
  },
  {
    getFormattedString: ({ duration }): string => duration,
    id: 'duration',
    label: t(labelDuration),
    sortField: 'last_status_change',
    type: ColumnType.string,
    width: 125,
  },
  {
    getFormattedString: ({ tries }): string => tries,
    id: 'tries',
    label: t(labelTries),
    type: ColumnType.string,
    width: 125,
  },
  {
    getFormattedString: ({ last_check }): string => last_check,
    id: 'last_check',
    label: t(labelLastCheck),
    type: ColumnType.string,
    width: 125,
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
    type: ColumnType.string,
  },
  {
    Component: StateColumn,
    getRenderComponentOnRowUpdateCondition: T,
    id: 'state',
    label: t(labelState),
    sortable: false,
    type: ColumnType.component,
    width: 80,
  },
];

export const defaultSortField = 'status_severity_code';
export const defaultSortOrder = 'asc';
