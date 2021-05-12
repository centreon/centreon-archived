import * as React from 'react';

import { Grid } from '@material-ui/core';

import { SelectEntry, useMemoComponent } from '@centreon/ui';

import { useResourceContext } from '../../Context';

import CriteriasMultiSelect from './CriteriasMultiSelect';
import Criteria from './Criteria';
import { Criteria as CriteriaInterface } from './models';

interface Props {
  criterias: Array<CriteriaInterface>;
}

const CriteriasContent = ({ criterias }: Props): JSX.Element => {
  return (
    <>
      {criterias.map(({ name, value }) => {
        return (
          <Grid item key={name}>
            <Criteria name={name} value={value as Array<SelectEntry>} />
          </Grid>
        );
      })}
      <Grid item>
        <CriteriasMultiSelect />
      </Grid>
    </>
  );
};

const Criterias = (): JSX.Element => {
  const { setFilter, setNextSearch, getMultiSelectCriterias } =
    useResourceContext();

  const criterias = getMultiSelectCriterias();

  return useMemoComponent({
    Component: (
      <CriteriasContent
        criterias={criterias}
        setFilter={setFilter}
        setNextSearch={setNextSearch}
      />
    ),
    memoProps: [criterias],
  });
};

export default Criterias;
