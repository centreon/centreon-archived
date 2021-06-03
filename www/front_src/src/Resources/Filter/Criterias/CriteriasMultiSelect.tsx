import * as React from 'react';

import { useTranslation } from 'react-i18next';
import {
  difference,
  flip,
  includes,
  isNil,
  map,
  pipe,
  prop,
  propSatisfies,
  reject,
  toPairs,
} from 'ramda';

import AddIcon from '@material-ui/icons/AddCircle';

import {
  IconPopoverMultiSelectField,
  SelectEntry,
  useMemoComponent,
} from '@centreon/ui';

import { labelNewFilter, labelSelectCriterias } from '../../translatedLabels';
import { useResourceContext } from '../../Context';
import { FilterState } from '../useFilter';
import { allFilter } from '../models';

import {
  CriteriaById,
  CriteriaDisplayProps,
  selectableCriterias,
} from './models';
import { getAllCriterias } from './default';

const toCriteriaPairs = (criteriaById: CriteriaById) =>
  toPairs<CriteriaDisplayProps>(criteriaById);

const isIn = flip(includes);
const nameIsIn = (names: Array<string>) => propSatisfies(isIn(names), 'name');

const CriteriasMultiSelectContent = ({
  filter,
  setFilter,
}: Pick<FilterState, 'filter' | 'setFilter'>): JSX.Element => {
  const { t } = useTranslation();

  const options = pipe(
    toCriteriaPairs,
    map(([id, { label }]) => ({ id, name: t(label) })),
  )(selectableCriterias);

  const selectedCriterias = filter.criterias
    .filter(({ name }) => !isNil(selectableCriterias[name]))
    .map(({ name }) => ({
      id: name,
      name: t(selectableCriterias[name].label),
    }));

  const changeSelectedCriterias = (updatedCriterias: Array<SelectEntry>) => {
    const { criterias } = filter;
    const updatedNames = map(prop('id'), updatedCriterias) as Array<string>;

    const currentNames = pipe(
      map(prop('name')),
      reject((name) => isNil(selectableCriterias[name as string])),
    )(filter.criterias);

    const criteriaNamesToAdd = difference(updatedNames, currentNames);
    const criteriaNamesToRemove = difference(currentNames, updatedNames);

    const criteriasWithoutRemoved = reject(
      nameIsIn(criteriaNamesToRemove),
      criterias,
    );

    const criteriasToAdd = getAllCriterias()
      .filter(nameIsIn(criteriaNamesToAdd))
      .map((criteria) => {
        return { ...criteria, value: [] };
      });

    setFilter({
      ...filter,
      criterias: [...criteriasWithoutRemoved, ...criteriasToAdd],
      id: '',
      name: labelNewFilter,
    });
  };

  const resetCriteria = (): void => {
    setFilter(allFilter);
  };

  return (
    <IconPopoverMultiSelectField
      icon={<AddIcon />}
      options={options}
      popperPlacement="bottom-start"
      title={t(labelSelectCriterias)}
      value={selectedCriterias}
      onChange={changeSelectedCriterias}
      onReset={resetCriteria}
    />
  );
};

const CriteriasMultiSelect = (): JSX.Element => {
  const { filter, setFilter } = useResourceContext();

  return useMemoComponent({
    Component: (
      <CriteriasMultiSelectContent filter={filter} setFilter={setFilter} />
    ),
    memoProps: [filter],
  });
};

export default CriteriasMultiSelect;
