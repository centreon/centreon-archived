/* eslint-disable react/jsx-key */
import * as React from 'react';

import { equals } from 'ramda';
import { useUpdateAtom } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import { Grid, Chip, Typography, useTheme, Tooltip } from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';
import IconFilterList from '@material-ui/icons/FilterList';

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
            spacing={1}
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
                    minWidth: theme.spacing(6),
                    overflow: 'hidden',
                  }}
                >
                  <Typography
                    style={{
                      color:
                        hoveredGroupId === group.id ? 'transparent' : 'unset',
                      gridArea: '1/1',
                      maxWidth: theme.spacing(12),
                    }}
                    variant="body2"
                  >
                    {group.name}
                  </Typography>

                  {hoveredGroupId === group.id && (
                    <Grid
                      style={{
                        backgroundColor: theme.palette.primary.main,
                        display: 'flex',
                        gridArea: '1/1',
                      }}
                    >
                      <Tooltip placement="top" title={title}>
                        <Chip
                          icon={
                            <IconFilterList
                              fontSize="small"
                              style={{ color: 'unset' }}
                            />
                          }
                          style={{
                            backgroundColor: theme.palette.primary.main,
                            color: 'unset',
                            display: 'grid',
                            justifyItems: 'center',
                            minWidth: theme.spacing(3),
                            overflow: 'hidden',
                          }}
                          onClick={(): void => filterByGroup(group)}
                        />
                      </Tooltip>
                      <Tooltip placement="top" title={titleSettings}>
                        <Chip
                          icon={
                            <SettingsIcon
                              fontSize="small"
                              style={{ color: 'unset' }}
                            />
                          }
                          style={{
                            backgroundColor: theme.palette.primary.main,
                            color: 'unset',
                            display: 'grid',
                            justifyItems: 'center',
                            minWidth: theme.spacing(3),
                            overflow: 'hidden',
                          }}
                          onClick={(): void => filterByGroup(group)}
                        />
                      </Tooltip>
                    </Grid>
                  )}
                </div>
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

/*
const classes = useStyles();
  const { t } = useTranslation();

  const resourceUris = path<ResourceUris>(
    ['links', 'uris'],
    details,
  ) as ResourceUris;

  const resourceConfigurationUri = prop('configuration', resourceUris);

  const resourceConfigurationUriTitle = isNil(resourceConfigurationUri)
    ? t(labelActionNotPermitted)
    : '';

  const resourceConfigurationIconColor = isNil(resourceConfigurationUri)
    ? 'disabled'
    : 'primary';
    */
