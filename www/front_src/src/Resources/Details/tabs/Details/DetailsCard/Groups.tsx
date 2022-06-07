import { equals } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Grid, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { labelGroups } from '../../../../translatedLabels';
import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { ResourceDetails } from '../../../models';
import { ResourceType } from '../../../../models';

import GroupChip from './GroupChip';

interface Props {
  details: ResourceDetails | undefined;
}

const useStyles = makeStyles((theme) => ({
  groups: {
    display: 'flex',
    padding: theme.spacing(1),
  },
}));

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
