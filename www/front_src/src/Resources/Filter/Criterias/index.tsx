import * as React from 'react';

import { ParentSize } from '@visx/visx';
import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';

import { Button, Grid } from '@material-ui/core';

import {
  MultiAutocompleteField,
  MultiConnectedAutocompleteField,
  SelectEntry,
} from '@centreon/ui';

import { useResourceContext } from '../../Context';
import { labelClear, labelOpen } from '../../translatedLabels';
import { useStyles } from '..';
import { allFilter } from '../models';

import CriteriasMultiSelect from './CriteriasMultiSelect';
import { criteriaValueNameById, selectableCriterias } from './models';

const Criterias = (): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const {
    setFilter,
    setCriteria,
    setNextSearch,
    setNewFilter,
    getMultiSelectCriterias,
  } = useResourceContext();

  const clearAllFilters = (): void => {
    setFilter(allFilter);
    setNextSearch('');
  };

  const getUntranslated = (values: Array<SelectEntry>): Array<SelectEntry> => {
    return values.map(({ id }) => ({
      id,
      name: criteriaValueNameById[id],
    }));
  };

  const changeCriteria = (name) => (_, value): void => {
    setCriteria({ name, value: getUntranslated(value) });
    setNewFilter();
  };

  const getTranslated = (values: Array<SelectEntry>): Array<SelectEntry> => {
    return values.map(({ id, name }) => ({
      id,
      name: t(name),
    }));
  };

  return (
    <ParentSize>
      {({ width }): JSX.Element => {
        const limitTags = width < 1000 ? 1 : 2;

        return (
          <Grid container spacing={1} alignItems="center">
            {getMultiSelectCriterias().map(({ name, value }) => {
              const {
                label,
                options,
                buildAutocompleteEndpoint,
              } = selectableCriterias[name];

              const commonProps = {
                limitTags,
                label: t(label),
                className: classes.field,
                openText: `${t(labelOpen)} ${t(label)}`,
                onChange: changeCriteria(name),
                value,
              };

              if (isNil(options)) {
                const getEndpoint = ({ search, page }) =>
                  buildAutocompleteEndpoint({
                    search,
                    page,
                    limit: 10,
                  });
                return (
                  <MultiConnectedAutocompleteField
                    key={name}
                    getEndpoint={getEndpoint}
                    value={value || []}
                    field="name"
                    {...commonProps}
                  />
                );
              }

              return (
                <MultiAutocompleteField
                  key={name}
                  options={getTranslated(options)}
                  value={options || []}
                  {...commonProps}
                />
              );
            })}

            <Grid item>
              <CriteriasMultiSelect />
            </Grid>
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
