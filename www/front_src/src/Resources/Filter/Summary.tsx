import * as React from 'react';

import { isEmpty, isNil, propEq } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  Avatar,
  Chip,
  makeStyles,
  Typography,
  Tooltip,
} from '@material-ui/core';
import SearchIcon from '@material-ui/icons/Search';

import { SelectEntry, useMemoComponent } from '@centreon/ui';

import { ResourceContext, useResourceContext } from '../Context';
import {
  labelHostGroup,
  labelResource,
  labelServiceGroup,
  labelState,
  labelStatus,
} from '../translatedLabels';

import { CriteriaValue } from './Criterias/models';

type Props = Pick<ResourceContext, 'filter'>;

const useStyles = makeStyles((theme) => ({
  criteriaValues: {
    display: 'flex',
    flexDirection: 'column',
  },
  summary: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(1),
    gridTemplateColumns: 'repeat(auto-fit, minmax(25px, auto))',
    overflow: 'hidden',
  },
}));

const FilterSummary = ({ filter }: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const getCriteriaValue = (name: string): CriteriaValue | undefined => {
    return filter.criterias.find(propEq('name', name))?.value;
  };

  const search = getCriteriaValue('search');
  const resourceTypes = getCriteriaValue('resource_types');
  const states = getCriteriaValue('states');
  const statuses = getCriteriaValue('statuses');
  const hostGroups = getCriteriaValue('host_groups');
  const serviceGroups = getCriteriaValue('service_groups');

  const criterias = [
    { label: labelResource, value: resourceTypes },
    { label: labelState, value: states },
    { label: labelStatus, value: statuses },
    { label: labelHostGroup, value: hostGroups },
    { label: labelServiceGroup, value: serviceGroups },
  ]
    .filter(({ value }) => {
      return !isNil(value) && !isEmpty(value);
    })
    .map(({ label, value }) => {
      const values = value as Array<SelectEntry>;

      return (
        <Tooltip
          key={label}
          title={
            <div className={classes.criteriaValues}>
              {values?.map(({ name }) => (
                <Typography key={name} variant="caption">
                  {name}
                </Typography>
              ))}
            </div>
          }
        >
          <span>
            <Chip
              disabled
              avatar={<Avatar>{value?.length}</Avatar>}
              label={t(label)}
              size="small"
            />
          </span>
        </Tooltip>
      );
    });

  return (
    <div className={classes.summary}>
      {!isNil(search) && search !== '' && (
        <Tooltip title={search}>
          <span>
            <Chip
              disabled
              avatar={<SearchIcon />}
              label={search}
              size="small"
            />
          </span>
        </Tooltip>
      )}
      {criterias}
    </div>
  );
};

const MemoizedFilterSummary = (): JSX.Element => {
  const { filter } = useResourceContext();

  return useMemoComponent({
    Component: <FilterSummary filter={filter} />,
    memoProps: [filter],
  });
};

export default MemoizedFilterSummary;
