import * as React from 'react';

import { ParentSize } from '@visx/visx';
import { useTranslation } from 'react-i18next';

import { Button, Grid } from '@material-ui/core';

import { SelectEntry, useMemoComponent } from '@centreon/ui';

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
  const { t } = useTranslation();

  const clearAllFilters = (): void => {
    setFilter(allFilter);
    setNextSearch('');
  };

  return (
    <ParentSize>
      {({ width }): JSX.Element => {
        return (
          <Grid container alignItems="center" spacing={1}>
            <Grid item>
              <CriteriasMultiSelect />
            </Grid>
            {criterias.map(({ name, value }) => {
              return (
                <Grid item key={name}>
                  <Criteria
                    name={name}
                    parentWidth={width}
                    value={value as Array<SelectEntry>}
                  />
                </Grid>
              );
            })}
            <Grid item>
              <Button color="primary" size="small" onClick={clearAllFilters}>
                {t(labelClear)}
              </Button>
            </Grid>
          </Grid>
        );
      }}
    </ParentSize>
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
