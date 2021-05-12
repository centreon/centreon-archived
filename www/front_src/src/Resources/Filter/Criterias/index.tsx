import * as React from 'react';

import { useTranslation } from 'react-i18next';

import ClearIcon from '@material-ui/icons/Clear';
import { Grid } from '@material-ui/core';

import { IconButton, SelectEntry, useMemoComponent } from '@centreon/ui';

import { ResourceContext, useResourceContext } from '../../Context';
import { labelClear } from '../../translatedLabels';
import { allFilter } from '../models';

import CriteriasMultiSelect from './CriteriasMultiSelect';
import Criteria from './Criteria';
import { Criteria as CriteriaInterface } from './models';

interface Props extends Pick<ResourceContext, 'setFilter' | 'setNextSearch'> {
  criterias: Array<CriteriaInterface>;
}

const CriteriasContent = ({
  setFilter,
  setNextSearch,
  criterias,
}: Props): JSX.Element => {
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
