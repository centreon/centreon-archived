import { useTranslation } from 'react-i18next';

import { Grid, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { Category, Group } from '../../../models';

import GroupChip from './GroupChip';

interface Props {
  getType: () => CriteriaNames;
  groups?: Array<Category | Group>;
  title: string;
}

const useStyles = makeStyles((theme) => ({
  groups: {
    display: 'flex',
    padding: theme.spacing(1),
  },
}));

const GroupChips = ({ groups = [], title, getType }: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const type = getType();

  return (
    <Grid container className={classes.groups} spacing={1}>
      <Grid item xs={12}>
        <Typography color="textSecondary" variant="body1">
          {t(title)}
        </Typography>
      </Grid>
      {groups?.map((group) => {
        return <GroupChip group={group} key={group.id} type={type} />;
      })}
    </Grid>
  );
};

export default GroupChips;
