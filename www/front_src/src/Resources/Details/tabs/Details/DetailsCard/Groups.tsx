import * as React from 'react';

import { equals, isNil } from 'ramda';
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
import { Group, ResourceDetails } from '../../../models';
import { ResourceType } from '../../../../models';

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
    color: 'unset',
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
  const classes = useStyles();

  const { t } = useTranslation();

  const [hoverChip, setHoverChip] = React.useState<boolean>(false);

  return (
    <Grid container className={classes.groups} spacing={1}>
      <Grid item xs={12}>
        <Typography color="textSecondary" variant="body1">
          {t(labelGroups)}
        </Typography>
      </Grid>
      {details?.groups?.map((group) => {
        return (
          <Grid item className={classes.chipsGroups} key={group.id}>
            <Chip
              color="primary"
              label={
                hoverChip && (
                  <div className={classes.groupsChipLabel}>
                    <Tooltip title={group.name}>
                      <Typography
                        className={classes.groupsChipAction}
                        variant="body2"
                      >
                        {group.name}
                      </Typography>
                    </Tooltip>
                  </div>
                )
              }
              onMouseEnter={(): void => setHoverChip(true)}
              onMouseLeave={(): void => setHoverChip(false)}
            />
          </Grid>
        );
      })}
    </Grid>
  );
};
interface GroupsChipProps {
  group: Group;
  type: string;
}

const GroupChip = ({ group, type }: GroupsChipProps): JSX.Element => {
  const theme = useTheme();
  const classes = useStyles();
  const { t } = useTranslation();

  const [hoverChip, setHoverChip] = React.useState<boolean>(false);

  const setCriteriaAndNewFilter = useUpdateAtom(
    setCriteriaAndNewFilterDerivedAtom,
  );

  const filterByGroup = (): void => {
    setCriteriaAndNewFilter({
      name: equals(type, ResourceType.host)
        ? CriteriaNames.hostGroups
        : CriteriaNames.serviceGroups,
      value: [group],
    });
  };

  return (
    <Grid item className={classes.chipsGroups}>
      <Chip
        color="primary"
        label={
          hoverChip !== true ? (
            <div className={classes.groupsChipLabel}>
              <Grid className={classes.test} key={group.id}>
                <IconButton
                  style={{ color: theme.palette.common.white }}
                  title={t(labelFilter)}
                  onClick={(): void => filterByGroup()}
                >
                  <IconFilterList fontSize="small" />
                </IconButton>
                <IconButton
                  style={{ color: theme.palette.common.white }}
                  title={t(labelConfigure)}
                  onClick={(): void => {
                    window.location.href = group.configuration_uri as string;
                  }}
                >
                  <SettingsIcon fontSize="small" />
                </IconButton>
              </Grid>
            </div>
          ) : (
            <Groups />
          )
        }
        onMouseEnter={(): void => setHoverChip(true)}
        onMouseLeave={(): void => setHoverChip(false)}
      />
    </Grid>
  );
};

export default GroupChip;
