import { useTranslation } from 'react-i18next';

import { Grid, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { Category, Group } from '../../../models';

import DetailsChip from './DetailsChip';

interface Props {
  getType: () => CriteriaNames;
  groupsObject?: Array<Category | Group>;
  title: string;
}

const useStyles = makeStyles((theme) => ({
  groups: {
    display: 'flex',
    padding: theme.spacing(1),
  },
}));

const DetailsChips = ({
  groupsObject = [],
  title,
  getType,
}: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  return (
    <Grid container className={classes.groups} spacing={1}>
      <Grid item xs={12}>
        <Typography color="textSecondary" variant="body1">
          {t(title)}
        </Typography>
      </Grid>
      {groupsObject?.map((group) => {
        return <DetailsChip group={group} key={group.id} type={getType()} />;
      })}
    </Grid>
  );
};

export default DetailsChips;
