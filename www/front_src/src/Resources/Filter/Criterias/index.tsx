import * as React from 'react';

import { ParentSize } from '@visx/visx';
import { useTranslation } from 'react-i18next';

import { Button, Grid } from '@material-ui/core';

import { SelectEntry } from '@centreon/ui/src';

import { useResourceContext } from '../../Context';
import { labelClear } from '../../translatedLabels';
import { allFilter } from '../models';

import CriteriasMultiSelect from './CriteriasMultiSelect';
import Criteria from './Criteria';

const Criterias = (): JSX.Element => {
  const { t } = useTranslation();

  const {
    setFilter,
    setNextSearch,
    getMultiSelectCriterias,
  } = useResourceContext();

  const clearAllFilters = (): void => {
    setFilter(allFilter);
    setNextSearch('');
  };

  return (
    <ParentSize>
      {({ width }): JSX.Element => {
        return (
          <Grid container spacing={1} alignItems="center">
            <Grid item>
              <CriteriasMultiSelect />
            </Grid>
            {getMultiSelectCriterias().map(({ name, value }) => {
              return (
                <Grid item key={name}>
                  <Criteria
                    name={name}
                    value={value as Array<SelectEntry>}
                    parentWidth={width}
                  />
                </Grid>
              );
            })}
            <Grid item>
              <Button color="primary" onClick={clearAllFilters} size="small">
                {t(labelClear)}
              </Button>
            </Grid>
          </Grid>
        );
      }}
    </ParentSize>
  );
};

export default Criterias;
