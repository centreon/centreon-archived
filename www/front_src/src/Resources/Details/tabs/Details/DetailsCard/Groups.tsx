import { useCallback } from 'react';

import { equals } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useUpdateAtom } from 'jotai/utils';

import { Grid, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { setCriteriaAndNewFilterDerivedAtom } from '../../../../Filter/filterAtoms';
import { labelGroups } from '../../../../translatedLabels';
import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { Group, ResourceDetails } from '../../../models';
import { ResourceType } from '../../../../models';

import DetailsChip from './DetailsChip';

interface Props {
  details: ResourceDetails | undefined;
  group: Group;
}

const useStyles = makeStyles((theme) => ({
  groups: {
    display: 'flex',
    padding: theme.spacing(1),
  },
}));

const Groups = ({ details, group }: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();
  const setCriteriaAndNewFilter = useUpdateAtom(
    setCriteriaAndNewFilterDerivedAtom,
  );

  const filterByGroup = useCallback(
    (type: CriteriaNames) => (): void => {
      const groupType = equals(details?.type, ResourceType.host)
        ? CriteriaNames.hostGroups
        : CriteriaNames.serviceGroups;

      setCriteriaAndNewFilter({
        name: groupType,
        value: [group],
      });
    },
    [group, details?.type],
  );

  const configureGroup = useCallback((): void => {
    window.location.href = group.configuration_uri as string;
  }, [group.configuration_uri]);

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
            goToConfiguration={configureGroup}
            id={id}
            key={id}
            name={name}
            setFilter={filterByGroup(groupType)}
          />
        );
      })}
    </Grid>
  );
};

export default Groups;
