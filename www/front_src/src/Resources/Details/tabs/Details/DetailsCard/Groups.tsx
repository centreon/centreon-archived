import * as React from 'react';

import { equals } from 'ramda';

import { Grid, Chip } from '@material-ui/core';

import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { ResourceDetails } from '../../../models';
import { NamedEntity, ResourceType } from '../../../../models';
import { useResourceContext } from '../../../../Context';

interface Props {
  details: ResourceDetails;
}

const Groups = ({ details }: Props): JSX.Element => {
  const { setCriteriaAndNewFilter } = useResourceContext();

  const filterByGroup = (group: NamedEntity): void => {
    setCriteriaAndNewFilter({
      name: equals(details.type, ResourceType.host)
        ? CriteriaNames.hostGroups
        : CriteriaNames.serviceGroups,
      value: [group],
    });
  };

  return (
    <Grid container spacing={1}>
      {details.groups?.map((group) => {
        return (
          <Grid item key={group.name}>
            <Chip
              clickable
              color="primary"
              label={group.name}
              onClick={(): void => filterByGroup(group)}
            />
          </Grid>
        );
      })}
    </Grid>
  );
};

export default Groups;
