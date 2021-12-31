/* eslint-disable react/jsx-key */
import * as React from 'react';

import { equals, isNil, path, prop } from 'ramda';
import { useUpdateAtom } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import { Grid, Chip, makeStyles, Tooltip, Link } from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';
import IconFilterList from '@material-ui/icons/FilterList';

import { IconButton } from '@centreon/ui';

import {
  labelActionNotPermitted,
  labelConfigure,
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

const GroupsOnHover = ({ details }: Props): JSX.Element => {
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

  return (
    <Grid container spacing={1}>
      {details?.groups?.map((group) => {
        return (
          <div className={classes.actions}>
            <Tooltip title={resourceConfigurationUriTitle}>
              <div className={classes.resourceNameConfigurationIcon}>
                <Link
                  aria-label={`${t(labelConfigure)}_${details.name}`}
                  className={classes.resourceNameConfigurationLink}
                  href={resourceConfigurationUri}
                >
                  <SettingsIcon
                    color={resourceConfigurationIconColor}
                    fontSize="small"
                  />
                </Link>
              </div>
            </Tooltip>
            <IconButton
              ariaLabel={t(labelConfigure)}
              color="primary"
              title={labelConfigure}
              onClick={(): void => filterByGroup(group)}
            >
              <IconFilterList fontSize="small" />
            </IconButton>
          </div>
        );
      })}
      ;
    </Grid>
  );
};

const Groups = ({ details }: Props): JSX.Element => {
  const [isHovered, setIsHovered] = React.useState<boolean>(false);

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

  return (
  <Grid container spacing={1}>
      {isHovered ? (
      <GroupsOnHover details={details} />
      ) : (
      {details.groups?.map((group) => {
        return (
          <Grid item key={group.name}>
            <Chip
              clickable
              color="primary"
              label={group.name}
              size="small"
              onClick={(): void => filterByGroup(group)}
              onMouseEnter={(): void => setIsHovered(true)}
              onMouseLeave={(): void => setIsHovered(false)}
            />
          </Grid>
        );
      })}
      )}
    </Grid>
  );
};

export default Groups;
