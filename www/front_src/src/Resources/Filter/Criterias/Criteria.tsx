import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';

import {
  MultiAutocompleteField,
  MultiConnectedAutocompleteField,
  SelectEntry,
} from '@centreon/ui';

import { useStyles } from '..';
import { useResourceContext } from '../../Context';
import { labelOpen } from '../../translatedLabels';

import { criteriaValueNameById, selectableCriterias } from './models';

interface Props {
  name: string;
  value: Array<SelectEntry>;
  parentWidth: number;
}

const Criteria = ({ name, value, parentWidth }: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();
  const limitTags = parentWidth < 1000 ? 1 : 2;

  const { setCriteria, setNewFilter } = useResourceContext();

  const getTranslated = (values: Array<SelectEntry>): Array<SelectEntry> => {
    return values.map((entry) => ({
      id: entry.id,
      name: t(entry.name),
    }));
  };

  const changeCriteria = (updatedValue): void => {
    setCriteria({ name, value: updatedValue });
    setNewFilter();
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
    limitTags,
    label: t(label),
    className: classes.field,
    openText: `${t(labelOpen)} ${t(label)}`,
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

export default Criteria;
