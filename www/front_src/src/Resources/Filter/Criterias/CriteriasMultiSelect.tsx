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
import { ClickAwayListener, Paper, Popper, useTheme } from '@material-ui/core';

import {
  IconButton,
  MultiAutocompleteField,
  useMemoComponent,
} from '@centreon/ui';

import {
  labelCriterias,
  labelNewFilter,
  labelSelectCriterias,
} from '../../translatedLabels';
import { useResourceContext } from '../../Context';
import { FilterState } from '../useFilter';

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
  const theme = useTheme();

  const [anchorEl, setAnchorEl] = React.useState();

  const options = pipe(
    toCriteriaPairs,
    map(([id, { label }]) => ({ id, name: t(label) })),
  )(selectableCriterias);

  const isOpen = Boolean(anchorEl);

  const close = (reason?): void => {
    const isClosedByInputClick = reason?.type === 'mousedown';

    if (isClosedByInputClick) {
      return;
    }
    setAnchorEl(undefined);
  };

  const toggle = (event): void => {
    if (isOpen) {
      close();
      return;
    }

    setAnchorEl(event.currentTarget);
  };

  const selectedCriterias = filter.criterias
    .filter(({ name }) => !isNil(selectableCriterias[name]))
    .map(({ name }) => ({
      id: name,
      name: t(selectableCriterias[name].label),
    }));

  const changeSelectedCriterias = (_, updatedCriterias) => {
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
      id: '',
      name: labelNewFilter,
      criterias: [...criteriasWithoutRemoved, ...criteriasToAdd],
    });
  };

  return (
    <ClickAwayListener onClickAway={close}>
      <div>
        <IconButton
          title={labelSelectCriterias}
          ariaLabel={labelSelectCriterias}
          onClick={toggle}
        >
          <AddIcon />
        </IconButton>
        <Popper
          style={{ zIndex: theme.zIndex.tooltip }}
          open={isOpen}
          anchorEl={anchorEl}
          placement="bottom-start"
        >
          <Paper>
            <MultiAutocompleteField
              onClose={close}
              label={t(labelCriterias)}
              options={options}
              onChange={changeSelectedCriterias}
              value={selectedCriterias}
              open={isOpen}
              limitTags={1}
            />
          </Paper>
        </Popper>
      </div>
    </ClickAwayListener>
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
