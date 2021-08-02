import * as React from 'react';

import { isEmpty, propEq, pick, find, equals, last } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  ClickAwayListener,
  makeStyles,
  MenuItem,
  Paper,
  Popper,
} from '@material-ui/core';

import { MemoizedFilter, SearchField } from '@centreon/ui';

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
import { getAutocompleteSuggestions } from './Criterias/searchQueryLanguage';

const useStyles = makeStyles((theme) => ({
  container: {
    alignItems: 'center',
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(1),
    gridTemplateColumns: 'auto auto auto 1fr',
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

  const [isSearchFieldFocus, setIsSearchFieldFocus] = React.useState(false);
  const [anchorEl, setAnchorEl] = React.useState<HTMLDivElement | null>(null);
  const searchRef = React.useRef<HTMLInputElement>();
  const [autoCompleteSuggestions, setAutoCompleteSuggestions] = React.useState<
    Array<string>
  >([]);
  const [cursorPosition, setCursorPosition] = React.useState(0);

  const open = Boolean(anchorEl);

  React.useEffect(() => {
    setAutoCompleteSuggestions(
      getAutocompleteSuggestions({
        cursorPosition,
        search,
      }),
    );
  }, [search, cursorPosition]);

  const memoProps = [
    customFilters,
    customFiltersLoading,
    search,
    cursorPosition,
    autoCompleteSuggestions,
    open,
  ];

  React.useEffect(() => {
    if (equals(autoCompleteSuggestions, []) || !isSearchFieldFocus) {
      setAnchorEl(null);

      return;
    }

    setAnchorEl(searchRef?.current as HTMLDivElement);
  }, [autoCompleteSuggestions]);

  const acceptAutocompleteSuggestionAtIndex = (index) => {
    setNewFilter();

    const acceptedSuggestion = autoCompleteSuggestions[index];

    if (search[cursorPosition] === ',') {
      setSearch(search + acceptedSuggestion);
      return;
    }

    const searchBeforeCursor = search.slice(0, cursorPosition + 1);
    const lastExpression = last(searchBeforeCursor.split(' ')) || '';
    const lastExpressionUntilCursor = lastExpression.slice(0, cursorPosition);

    const lastExpressionAfterSeparator =
      lastExpression.endsWith(':') ||
      lastExpression.endsWith(',') ||
      acceptedSuggestion.startsWith(',')
        ? ''
        : last(lastExpressionUntilCursor.split(/:|,/)) || '';

    const cursorShift =
      acceptedSuggestion.length - lastExpressionAfterSeparator.length;
    const isLastExpressionEmpty = lastExpressionAfterSeparator === '';
    const searchCutPosition = isLastExpressionEmpty
      ? cursorPosition + 1
      : cursorPosition;

    const searchWithAcceptedSuggestion = [
      search.slice(0, searchCutPosition),
      acceptedSuggestion.slice(
        lastExpressionAfterSeparator.length,
        acceptedSuggestion.length,
      ),
      search.slice(searchCutPosition),
    ].join('');

    setSearch(searchWithAcceptedSuggestion);

    setCursorPosition(cursorPosition + cursorShift);
  };

  const inputKey = (event: React.KeyboardEvent): void => {
    const enterKeyPressed = event.key === 'Enter';
    const tabKeyPressed = event.key === 'Tab';

    if (tabKeyPressed && !isEmpty(autoCompleteSuggestions)) {
      acceptAutocompleteSuggestionAtIndex(0);

      event.preventDefault();
    }

    if (enterKeyPressed) {
      applyCurrentFilter();
    }
  };

  const prepareSearch = (event): void => {
    const { value } = event.target;

    setSearch(value);
    setCursorPosition(searchRef?.current?.selectionStart || 0);

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
          <Criterias />
          <ClickAwayListener onClickAway={closeSuggestionPopover}>
            <div>
              <SearchField
                fullWidth
                inputRef={searchRef as React.RefObject<HTMLInputElement>}
                placeholder={t(labelSearch)}
                value={search}
                onBlur={() => setIsSearchFieldFocus(false)}
                onChange={prepareSearch}
                onFocus={() => setIsSearchFieldFocus(true)}
                onKeyDown={inputKey}
              />
              <Popper
                anchorEl={anchorEl}
                open={open}
                style={{
                  width: searchRef?.current?.clientWidth,
                  zIndex: 1000,
                }}
              >
                <Paper square>
                  {autoCompleteSuggestions.map((suggestion, index) => {
                    return (
                      <MenuItem
                        key={suggestion}
                        onClick={() => {
                          acceptAutocompleteSuggestionAtIndex(index);
                          searchRef?.current?.focus();
                        }}
                      >
                        {suggestion}
                      </MenuItem>
                    );
                  })}
                </Paper>
              </Popper>
            </div>
          </ClickAwayListener>
        </div>
      }
      memoProps={memoProps}
    />
  );
};

export default Filter;
