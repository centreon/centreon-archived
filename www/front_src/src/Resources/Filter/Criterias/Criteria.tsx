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
  parentWidth: number;
  value: Array<SelectEntry>;
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

  const { label, options, buildAutocompleteEndpoint } = selectableCriterias[
    name
  ];

  const commonProps = {
    className: classes.field,
    label: t(label),
    limitTags,
    openText: `${t(labelOpen)} ${t(label)}`,
    value,
  };

  if (isNil(options)) {
    const getEndpoint = ({ search, page }) =>
      buildAutocompleteEndpoint({
        limit: 10,
        page,
        search,
      });
    return (
      <MultiConnectedAutocompleteField
        field="name"
        getEndpoint={getEndpoint}
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
        name={name}
        parentWidth={parentWidth}
        setCriteriaAndNewFilter={setCriteriaAndNewFilter}
        value={value}
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
