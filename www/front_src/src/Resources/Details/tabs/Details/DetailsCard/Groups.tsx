/* eslint-disable react/jsx-key */
import * as React from 'react';

import { equals } from 'ramda';
import { useUpdateAtom } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import { Grid, Chip, Typography, useTheme, Tooltip } from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';
import IconFilterList from '@material-ui/icons/FilterList';

import { IconButton } from '@centreon/ui';

import { labelConfigure, labelFilter } from '../../../../translatedLabels';
import { setCriteriaAndNewFilterDerivedAtom } from '../../../../Filter/filterAtoms';
import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { ResourceDetails } from '../../../models';
import { NamedEntity, ResourceType } from '../../../../models';

interface Props {
  details: ResourceDetails | undefined;
}

const Groups = ({ details }: Props): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();

  const [hoveredGroupId, setHoveredGroupId] = React.useState<number>();

  const setCriteriaAndNewFilter = useUpdateAtom(
    setCriteriaAndNewFilterDerivedAtom,
  );

  const filterByGroup = (group: NamedEntity): void => {
    setCriteriaAndNewFilter({
      name: equals(details?.type, ResourceType.host)
        ? CriteriaNames.hostGroups
        : CriteriaNames.serviceGroups,
      value: [group],
    });
  };

  const title = hoveredGroupId ? t(labelFilter) : '';
  const titleSettings = hoveredGroupId ? t(labelConfigure) : '';

  return (
    <Grid container spacing={1}>
      {details?.groups?.map((group) => {
        return (
          <Grid
            item
            key={group.id}
            style={{
              alignSelf: 'center',
              display: 'flex',
            }}
            onMouseEnter={(): void => setHoveredGroupId(group.id)}
            onMouseLeave={(): void => setHoveredGroupId(undefined)}
          >
            <Chip
              color="primary"
              label={
                <div
                  style={{
                    display: 'grid',
                    justifyItems: 'center',
                    minWidth: theme.spacing(7),
                    overflow: 'hidden',
                  }}
                >
                  <Tooltip title={group.name}>
                    <Typography
                      style={{
                        color:
                          hoveredGroupId === group.id ? 'transparent' : 'unset',
                        gridArea: '1/1',
                        maxWidth: theme.spacing(14),
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                      }}
                      variant="body2"
                    >
                      {group.name}
                    </Typography>
                  </Tooltip>

                  {hoveredGroupId === group.id && (
                    <Grid
                      style={{
                        backgroundColor: theme.palette.primary.main,
                        display: 'flex',
                        gap: theme.spacing(0.25),
                        gridArea: '1/1',
                      }}
                    >
                      <IconButton
                        style={{ color: theme.palette.common.white }}
                        title={t(labelConfigure)}
                        onClick={(): void => filterByGroup(group)}
                      >
                        <IconFilterList fontSize="small" />
                      </IconButton>
                      <IconButton
                        style={{ color: theme.palette.common.white }}
                        title={t(labelFilter)}
                        onClick={(): void => filterByGroup(group)}
                      >
                        <SettingsIcon fontSize="small" />
                      </IconButton>
                    </Grid>
                  )}
                </div>
              }
              style={
                {
                  // padding: 0,
                }
              }
              onMouseEnter={(): void => setHoveredGroupId(group.id)}
              onMouseLeave={(): void => setHoveredGroupId(undefined)}
            />
          </Grid>
        );
      })}
    </Grid>
  );
};

export default Groups;
