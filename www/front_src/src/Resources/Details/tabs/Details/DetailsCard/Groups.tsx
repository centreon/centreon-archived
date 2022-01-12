/* eslint-disable react/jsx-key */
import * as React from 'react';

import { equals } from 'ramda';
import { useUpdateAtom } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import {
  Grid,
  Chip,
  Typography,
  useTheme,
  Tooltip,
  makeStyles,
} from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';
import IconFilterList from '@material-ui/icons/FilterList';

import { IconButton } from '@centreon/ui';

import {
  labelConfigure,
  labelFilter,
  labelGroups,
} from '../../../../translatedLabels';
import { setCriteriaAndNewFilterDerivedAtom } from '../../../../Filter/filterAtoms';
import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { ResourceDetails } from '../../../models';
import { NamedEntity, ResourceType } from '../../../../models';

const useStyles = makeStyles((theme) => ({
  chipsGroups: {
    alignSelf: 'center',
    display: 'flex',
  },
  groups: {
    display: 'flex',
    padding: theme.spacing(1, 1, 1, 1),
  },
  groupsChipAction: {
    gridArea: '1/1',
    maxWidth: theme.spacing(14),
    overflow: 'hidden',
    textOverflow: 'ellipsis',
  },
  groupsChipLabel: {
    display: 'grid',
    justifyItems: 'center',
    minWidth: theme.spacing(7),
    overflow: 'hidden',
  },
  test: {
    backgroundColor: theme.palette.primary.main,
    display: 'flex',
    gap: theme.spacing(0.25),
    gridArea: '1/1',
  },
}));

interface Props {
  details: ResourceDetails | undefined;
}

const Groups = ({ details }: Props): JSX.Element => {
  const theme = useTheme();
  const classes = useStyles();

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

  return (
    <Grid container className={classes.groups} spacing={1}>
      <Grid item xs={12}>
        <Typography color="textSecondary" variant="body1">
          {t(labelGroups)}
        </Typography>
      </Grid>
      {details?.groups?.map((group) => {
        return (
          <Grid
            item
            className={classes.chipsGroups}
            key={group.id}
            onMouseEnter={(): void => setHoveredGroupId(group.id)}
            onMouseLeave={(): void => setHoveredGroupId(undefined)}
          >
            <Chip
              color="primary"
              label={
                <div className={classes.groupsChipLabel}>
                  <Tooltip title={group.name}>
                    <Typography
                      className={classes.groupsChipAction}
                      style={{
                        color:
                          hoveredGroupId === group.id ? 'transparent' : 'unset',
                      }}
                      variant="body2"
                    >
                      {group.name}
                    </Typography>
                  </Tooltip>

                  {hoveredGroupId === group.id && (
                    <Grid className={classes.test}>
                      <IconButton
                        style={{ color: theme.palette.common.white }}
                        title={t(labelFilter)}
                        onClick={(): void => filterByGroup(group)}
                      >
                        <IconFilterList fontSize="small" />
                      </IconButton>
                      <IconButton
                        style={{ color: theme.palette.common.white }}
                        title={t(labelConfigure)}
                        onClick={(): void => {
                          window.location.href =
                            group.configuration_uri as string;
                        }}
                      >
                        <SettingsIcon fontSize="small" />
                      </IconButton>
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
