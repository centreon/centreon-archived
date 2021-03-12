import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';

import {
  MultiAutocompleteField,
  MultiConnectedAutocompleteField,
  SelectEntry,
  useMemoComponent,
} from '@centreon/ui';

import { useStyles } from '..';
import { ResourceContext, useResourceContext } from '../../Context';
import { labelOpen } from '../../translatedLabels';

import { criteriaValueNameById, selectableCriterias } from './models';

interface Props {
  name: string;
  value: Array<SelectEntry>;
  parentWidth: number;
}

const CriteriaContent = ({
  name,
  value,
  parentWidth,
  setCriteriaAndNewFilter,
}: Props & Pick<ResourceContext, 'setCriteriaAndNewFilter'>): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();
  const limitTags = parentWidth < 1000 ? 1 : 2;

  const getTranslated = (values: Array<SelectEntry>): Array<SelectEntry> => {
    return values.map((entry) => ({
      id: entry.id,
      name: t(entry.name),
    }));
  };

  const changeCriteria = (updatedValue): void => {
    setCriteriaAndNewFilter({ name, value: updatedValue });
  };

  const getUntranslated = (values): Array<SelectEntry> => {
    return values.map(({ id }) => ({
      id,
      name: criteriaValueNameById[id],
    }));
  };

  const {
    label,
    options,
    buildAutocompleteEndpoint,
    autocompleteSearch,
  } = selectableCriterias[name];

  const commonProps = {
    limitTags,
    label: t(label),
    className: classes.field,
    openText: `${t(labelOpen)} ${t(label)}`,
    value,
    search: autocompleteSearch,
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
        getEndpoint={getEndpoint}
        field="name"
        onChange={(_, updatedValue) => {
          changeCriteria(updatedValue);
        }}
        {...commonProps}
      />
    );
  }

  return (
    <MultiAutocompleteField
      options={getTranslated(options)}
      onChange={(_, updatedValue) => {
        changeCriteria(getUntranslated(updatedValue));
      }}
      {...commonProps}
    />
  );
};

const Criteria = ({ value, name, parentWidth }: Props): JSX.Element => {
  const {
    setCriteriaAndNewFilter,
    getMultiSelectCriterias,
    nextSearch,
  } = useResourceContext();

  return useMemoComponent({
    Component: (
      <CriteriaContent
        setCriteriaAndNewFilter={setCriteriaAndNewFilter}
        value={value}
        name={name}
        parentWidth={parentWidth}
      />
    ),
    memoProps: [
      value,
      name,
      parentWidth,
      getMultiSelectCriterias(),
      nextSearch,
    ],
  });
};

export default Criteria;
