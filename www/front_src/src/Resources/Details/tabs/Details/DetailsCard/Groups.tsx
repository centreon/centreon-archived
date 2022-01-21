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

import IconButton from '@mui/material/IconButton';

import {
  labelConfigure,
  labelFilter,
  labelGroups,
} from '../../../../translatedLabels';
import { setCriteriaAndNewFilterDerivedAtom } from '../../../../Filter/filterAtoms';
import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { Group, ResourceDetails } from '../../../models';
import { ResourceType } from '../../../../models';
import GroupChip from './GroupChip';


const useStyles = makeStyles((theme) => ({
  groupChip: {
    alignSelf: 'center',
    display: 'flex',
  },
  groups: {
    display: 'flex',
    padding: theme.spacing(1, 1, 1, 1),
  },
  groupChipAction: {
    gridArea: '1/1',
    maxWidth: theme.spacing(14),
    overflow: 'hidden',
    textOverflow: 'ellipsis',
  },
  groupChipLabel: {
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
