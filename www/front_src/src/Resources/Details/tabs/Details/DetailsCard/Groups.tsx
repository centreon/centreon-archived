/* eslint-disable react/jsx-key */
import * as React from 'react';

import { equals, isNil, path, prop } from 'ramda';
import { useUpdateAtom } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import {
  Grid,
  Chip,
  makeStyles,
  Tooltip,
  Link,
  Typography,
  useTheme,
} from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';
import IconFilterList from '@material-ui/icons/FilterList';

import { IconButton, PopoverMenu } from '@centreon/ui';

import {
  labelActionNotPermitted,
  labelConfigure,
  labelFilter,
} from '../../../../translatedLabels';
import { setCriteriaAndNewFilterDerivedAtom } from '../../../../Filter/filterAtoms';
import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { ResourceDetails } from '../../../models';
import { NamedEntity, ResourceType, ResourceUris } from '../../../../models';

const useStyles = makeStyles((theme) => ({
  actions: {
    alignItems: 'center',
    display: 'flex',
    flexWrap: 'nowrap',
    gridGap: theme.spacing(0.75),
    justifyContent: 'center',
  },
  groupsCard: {
    alignItems: 'center',
    display: 'flex',
    width: '100%',
  },
  resourceNameConfigurationIcon: {
    alignSelf: 'center',
    display: 'flex',
    minWidth: theme.spacing(2.5),
  },
  resourceNameConfigurationLink: {
    height: theme.spacing(2.5),
  },
}));

interface Props {
  details: ResourceDetails | undefined;
}

const Groups = ({ details }: Props): JSX.Element => {
  const theme = useTheme();

  const [hoveredGroupId, setHoveredGroupId] = React.useState<number>();

  return (
    <Grid container spacing={1}>
      {details?.groups?.map((group) => {
        return (
          <Grid
            item
            key={group.id}
            onMouseEnter={(): void => setHoveredGroupId(group.id)}
            onMouseLeave={(): void => setHoveredGroupId(undefined)}
          >
            <Chip
              color="primary"
              label={
                <div
                  style={{
                    alignItems: 'center',
                    display: 'grid',
                    gridTemplateColumns: 'auto',
                    justifyItems: 'center',
                    padding: 0,
                  }}
                >
                  <Typography
                    style={{
                      color:
                        hoveredGroupId === group.id ? 'transparent' : 'unset',
                      gridArea: '1/1',
                    }}
                    variant="body2"
                  >
                    {group.name}
                  </Typography>

                  {hoveredGroupId === group.id && (
                    <div
                      style={{
                        backgroundColor: theme.palette.primary.main,
                        display: 'flex',
                        gap: theme.spacing(0.5),
                        gridArea: '1/1',
                        justifySelf: 'center',
                      }}
                    >
                      <IconButton
                        color="default"
                        onClick={(): void => console.log('FILTER')}
                      >
                        <IconFilterList fontSize="small" />
                      </IconButton>
                      <IconButton
                        color="default"
                        onClick={(): void => console.log('GO TO CONF')}
                      >
                        <SettingsIcon fontSize="small" />
                      </IconButton>
                    </div>
                  )}
                </div>
              }
              onMouseEnter={(): void => setHoveredGroupId(group.id)}
              onMouseLeave={(): void => setHoveredGroupId(undefined)}
            />{' '}
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

  const setCriteriaAndNewFilter = useUpdateAtom(
    setCriteriaAndNewFilterDerivedAtom,
  );

  const resourceUris = path<ResourceUris>(
    ['links', 'uris'],
    details,
  ) as ResourceUris;

  const resourceConfigurationUri = prop('configuration', resourceUris);

  const resourceConfigurationUriTitle = isNil(resourceConfigurationUri)
    ? t(labelActionNotPermitted)
    : '';

  const filterByGroup = (group: NamedEntity): void => {
    setCriteriaAndNewFilter({
      name: equals(details?.type, ResourceType.host)
        ? CriteriaNames.hostGroups
        : CriteriaNames.serviceGroups,
      value: [group],
    });
  };

  const resourceConfigurationIconColor = isNil(resourceConfigurationUri)
    ? 'disabled'
    : 'primary';
    */
