import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil, map, pipe, toPairs } from 'ramda';

import AddIcon from '@material-ui/icons/AddCircle';
import { ClickAwayListener, Popper } from '@material-ui/core';

import { IconButton, MultiAutocompleteField } from '@centreon/ui';

import { labelCriterias } from '../../translatedLabels';
import { useResourceContext } from '../../Context';

import {
  CriteriaById,
  CriteriaDisplayProps,
  selectableCriterias,
} from './models';

const toCriteriaPairs = (criteriaById: CriteriaById) =>
  toPairs<CriteriaDisplayProps>(criteriaById);

const CriteriasMultiSelect = (): JSX.Element => {
  const { t } = useTranslation();

  const { filter } = useResourceContext();

  const [anchorEl, setAnchorEl] = React.useState();

  const options = pipe(
    toCriteriaPairs,
    map(([id, { label }]) => ({ id, name: t(label) })),
  )(selectableCriterias);

  const isOpen = Boolean(anchorEl);

  const close = (): void => {
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

  return (
    <ClickAwayListener onClickAway={close}>
      <div>
        <IconButton title="Add criterias" onClick={toggle}>
          <AddIcon />
        </IconButton>
        <Popper
          placement="bottom"
          disablePortal
          open={isOpen}
          anchorEl={anchorEl}
        >
          <div style={{ backgroundColor: 'white' }}>
            <MultiAutocompleteField
              onClose={close}
              label={t(labelCriterias)}
              options={options}
              value={selectedCriterias}
              open={isOpen}
              limitTags={0}
            />
          </div>
        </Popper>
      </div>
    </ClickAwayListener>
  );
};

export default CriteriasMultiSelect;
