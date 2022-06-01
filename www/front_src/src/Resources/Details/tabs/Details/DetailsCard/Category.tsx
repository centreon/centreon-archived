import { useCallback } from 'react';

import { equals } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useUpdateAtom } from 'jotai/utils';

import { Grid, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { setCriteriaAndNewFilterDerivedAtom } from '../../../../Filter/filterAtoms';
import { labelGroups } from '../../../../translatedLabels';
import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { Category, ResourceDetails } from '../../../models';
import { ResourceType } from '../../../../models';

import DetailsChip from './DetailsChip';

interface Props {
  category: Category;
  details: ResourceDetails | undefined;
}

const useStyles = makeStyles((theme) => ({
  groups: {
    display: 'flex',
    padding: theme.spacing(1),
  },
}));

const Categories = ({ details, category }: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();
  const setCriteriaAndNewFilter = useUpdateAtom(
    setCriteriaAndNewFilterDerivedAtom,
  );

  const filterByCategory = useCallback(
    (type: CriteriaNames) => (): void => {
      setCriteriaAndNewFilter({
        name: type,
        value: [category],
      });
    },
    [category, details?.type],
  );
  const categoryType = equals(details?.type, ResourceType.host)
    ? CriteriaNames.hostCategories
    : CriteriaNames.serviceCategories;

  const configureCategory = useCallback((): void => {
    window.location.href = category.configuration_uri as string;
  }, [category.configuration_uri]);

  return (
    <Grid container className={classes.groups} spacing={1}>
      <Grid item xs={12}>
        <Typography color="textSecondary" variant="body1">
          {t(labelGroups)}
        </Typography>
      </Grid>
      {details?.groups?.map(({ id, name }) => {
        return (
          <DetailsChip
            goToConfiguration={configureCategory}
            id={id}
            key={id}
            name={name}
            setFilter={filterByCategory(categoryType)}
          />
        );
      })}
    </Grid>
  );
};

export default Categories;
