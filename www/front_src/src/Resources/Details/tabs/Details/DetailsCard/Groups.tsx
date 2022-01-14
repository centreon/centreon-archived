import * as React from 'react';

import { equals } from 'ramda';
import { useUpdateAtom } from 'jotai/utils';
import { useTranslation } from 'react-i18next';
import {
  Grid,
  Chip,
  Tooltip,
  Typography,
  useTheme,
} from '@mui/material';
import SettingsIcon from '@mui/icons-material/Settings';
import FilterListIcon from '@mui/icons-material/FilterList';

import makeStyles from '@mui/styles/makeStyles';

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
  iconAction: {
    backgroundColor: theme.palette.primary.main,
    display: 'flex',
    gap: theme.spacing(0.25),
    gridArea: '1/1',
  },
}));

interface GroupsChipProps {
  group: Group;
  type: string;
}

const GroupChip = ({ group, type }: GroupsChipProps): JSX.Element => {
  const theme = useTheme();
  const classes = useStyles();
  const { t } = useTranslation();

  const [isHovered, setIsHovered] = React.useState<boolean>(false);

  const setCriteriaAndNewFilter = useUpdateAtom(
    setCriteriaAndNewFilterDerivedAtom,
  );

  const filterByGroup = (): void => {
    setCriteriaAndNewFilter({
      name: type,
      value: [group],
    });
  };

  return (
    <Grid item className={classes.chipsGroups} key={group.id}>
      <Chip
        color="primary"
        label={
          <div className={classes.groupsChipLabel}>
            <Tooltip title={group.name}>
              <Typography
                className={classes.groupsChipAction}
                style={{ color: isHovered ? 'transparent' : 'unset' }}
                variant="body2"
              >
                {group.name}
              </Typography>
            </Tooltip>
            {isHovered === true && (
              <Grid className={classes.iconAction}>
                <IconButton
                  style={{ color: theme.palette.common.white }}
                  title={t(labelFilter)}
                  onClick={(): void => filterByGroup()}
                >
                  <FilterListIcon fontSize="small" />
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
            )}
          </div>
        }
        onMouseEnter={(): void => setIsHovered(true)}
        onMouseLeave={(): void => setIsHovered(false)}
      />
    </Grid>
  );
};

interface Props {
  details: ResourceDetails | undefined;
}

const Groups = ({ details }: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const groupType = equals(details?.type, ResourceType.host)
    ? CriteriaNames.hostGroups
    : CriteriaNames.serviceGroups;

  return (
    <Grid container className={classes.groups} spacing={1}>
      <Grid item xs={12}>
        <Typography color="textSecondary" variant="body1">
          {t(labelGroups)}
        </Typography>
      </Grid>
      {details?.groups?.map((group) => {
        return <GroupChip group={group} key={group.id} type={groupType} />;
      })}
    </Grid>
  );
};

export default Groups;
