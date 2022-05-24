import { equals } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Grid, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { labelCategorie } from '../../../../translatedLabels';
import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { ResourceDetails } from '../../../models';
import { ResourceType } from '../../../../models';

import CategorieChip from './CategorieChip';

interface Props {
  details: ResourceDetails | undefined;
}

const useStyles = makeStyles((theme) => ({
  categories: {
    display: 'flex',
    padding: theme.spacing(1),
  },
}));

const Groups = ({ details }: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const categorieType = equals(details?.type, ResourceType.host)
    ? CriteriaNames.hostCategories
    : CriteriaNames.serviceCategories;

  return (
    <Grid container className={classes.categories} spacing={1}>
      <Grid item xs={12}>
        <Typography color="textSecondary" variant="body1">
          {t(labelCategorie)}
        </Typography>
      </Grid>
      {details?.groups?.map((categorie) => {
        return (
          <CategorieChip
            categorie={categorie}
            key={categorie.id}
            type={categorieType}
          />
        );
      })}
    </Grid>
  );
};

export default Groups;
