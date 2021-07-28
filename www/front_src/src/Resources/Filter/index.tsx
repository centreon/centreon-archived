import * as React from 'react';

import { isEmpty, propEq, pick, find, equals } from 'ramda';
import { useTranslation } from 'react-i18next';
import PopupState, { bindTrigger, bindPopover } from 'material-ui-popup-state';

import {
  ClickAwayListener,
  List,
  ListItem,
  ListItemText,
  makeStyles,
  MenuItem,
  Paper,
  Popover,
  Popper,
} from '@material-ui/core';

import { MemoizedFilter, SearchField, TextField } from '@centreon/ui';

import {
  labelStateFilter,
  labelSearch,
  labelNewFilter,
  labelMyFilters,
} from '../translatedLabels';
import { useResourceContext } from '../Context';

import SaveFilter from './Save';
import FilterLoadingSkeleton from './FilterLoadingSkeleton';
import Criterias from './Criterias';
import {
  standardFilterById,
  unhandledProblemsFilter,
  resourceProblemsFilter,
  allFilter,
} from './models';
import SelectFilter from './Fields/SelectFilter';
import { getAutocompleteSuggestion } from './Criterias/searchQueryLanguage';

const useStyles = makeStyles((theme) => ({
  container: {
    alignItems: 'center',
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(1),
    gridTemplateColumns: 'auto auto 1fr auto',
    width: '100%',
  },
}));

const Filter = (): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const {
    applyFilter,
    customFilters,
    customFiltersLoading,
    setSearch,
    setNewFilter,
    currentFilter,
    search,
    applyCurrentFilter,
  } = useResourceContext();

  const [currentWord, setCurrentWord] = React.useState('');
  const [anchorEl, setAnchorEl] = React.useState<HTMLDivElement | null>(null);
  const searchRef = React.useRef<HTMLDivElement>();

  const open = Boolean(anchorEl);

  const memoProps = [customFilters, customFiltersLoading, search, open];

  // console.log(getAutocompleteSuggestion(currentWord));

  const autoCompleteSuggestions = getAutocompleteSuggestion(currentWord);

  React.useEffect(() => {
    if (equals(autoCompleteSuggestions, [])) {
      setAnchorEl(null);

      return;
    }

    setAnchorEl(searchRef?.current as HTMLDivElement);
  }, [autoCompleteSuggestions]);

  const requestSearchOnEnterKey = (event: React.KeyboardEvent): void => {
    const enterKeyPressed = event.key === 'Enter';
    const backspaceKeyPressed = event.key === 'Backspace';
    const tabKeyPressed = event.key === 'Tab';

    if (event.key === ' ' || backspaceKeyPressed || enterKeyPressed) {
      setCurrentWord('');
    } else {
      setCurrentWord(currentWord + event.key);
    }

    if (tabKeyPressed && !isEmpty(autoCompleteSuggestions)) {
      setSearch(search.replace(currentWord, autoCompleteSuggestions[0]));
      setNewFilter();
      setCurrentWord('');
      event.preventDefault();
    }

    if (enterKeyPressed) {
      applyCurrentFilter();
    }
  };

  const prepareSearch = (event): void => {
    const { value } = event.target;

    setSearch(value);

    setNewFilter();
  };

  const changeFilter = (event): void => {
    const filterId = event.target.value;

    const updatedFilter =
      standardFilterById[filterId] ||
      customFilters?.find(propEq('id', filterId));

    applyFilter(updatedFilter);
  };

  const translatedOptions = [
    unhandledProblemsFilter,
    resourceProblemsFilter,
    allFilter,
  ].map(({ id, name }) => ({ id, name: t(name) }));

  const customFilterOptions = isEmpty(customFilters)
    ? []
    : [
        {
          id: 'my_filters',
          name: t(labelMyFilters),
          type: 'header',
        },
        ...customFilters,
      ];

  const options = [
    { id: '', name: t(labelNewFilter) },
    ...translatedOptions,
    ...customFilterOptions,
  ];

  const canDisplaySelectedFilter = find(
    propEq('id', currentFilter.id),
    options,
  );

  const closeSuggestionPopover = () => {
    setCurrentWord('');
    setAnchorEl(null);
  };

  return (
    <MemoizedFilter
      content={
        <div className={classes.container}>
          <SaveFilter />
          {customFiltersLoading ? (
            <FilterLoadingSkeleton />
          ) : (
            <SelectFilter
              ariaLabel={t(labelStateFilter)}
              options={options.map(pick(['id', 'name', 'type']))}
              selectedOptionId={
                canDisplaySelectedFilter ? currentFilter.id : ''
              }
              onChange={changeFilter}
            />
          )}

          <ClickAwayListener onClickAway={closeSuggestionPopover}>
            <div>
              <TextField
                autoFocus
                fullWidth
                placeholder={t(labelSearch)}
                ref={searchRef as React.RefObject<HTMLDivElement>}
                value={search}
                onChange={prepareSearch}
                onKeyDown={requestSearchOnEnterKey}
              />
              <Popper
                anchorEl={anchorEl}
                // hideBackdrop
                // PaperProps={{
                //   style: {
                //     width: searchRef.current?.clientWidth,
                //   },
                // }}
                open={open}
                // anchorOrigin={{
                //   horizontal: 'center',
                //   vertical: 'bottom',
                // }}
                style={{
                  width: searchRef?.current?.clientWidth,
                  zIndex: 1000,
                }}
                // transformOrigin={{
                //   horizontal: 'center',
                //   vertical: 'top',
                // }}
              >
                {/* < dense> */}
                <Paper square>
                  {autoCompleteSuggestions.map((suggestion) => (
                    <MenuItem key={suggestion} onClick={() => undefined}>
                      {suggestion}
                    </MenuItem>
                  ))}
                </Paper>
                {/* </List> */}
              </Popper>
            </div>
          </ClickAwayListener>
          <Criterias />
        </div>
      }
      memoProps={memoProps}
    />
  );
};

export default Filter;
