import * as React from 'react';

import {
  isEmpty,
  propEq,
  pick,
  find,
  equals,
  last,
  inc,
  length,
  dec,
  isNil,
} from 'ramda';
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
  const [autocompleteAnchor, setAutocompleteAnchor] =
    React.useState<HTMLDivElement | null>(null);
  const searchRef = React.useRef<HTMLInputElement>();
  const [autoCompleteSuggestions, setAutoCompleteSuggestions] = React.useState<
    Array<string>
  >([]);
  const [cursorPosition, setCursorPosition] = React.useState(0);
  const [selectedSuggestionIndex, setSelectedSuggestionIndex] =
    React.useState(0);

  const open = Boolean(autocompleteAnchor);

  React.useEffect(() => {
    setSelectedSuggestionIndex(0);
    setAutoCompleteSuggestions(
      getAutocompleteSuggestions({
        cursorPosition,
        search,
      }),
    );
  }, [search, cursorPosition]);

  const updateCursorPosition = (): void => {
    setCursorPosition(searchRef?.current?.selectionStart || 0);
  };

  React.useEffect(() => {
    updateCursorPosition();
  }, [searchRef?.current?.selectionStart]);

  const memoProps = [
    customFilters,
    customFiltersLoading,
    search,
    cursorPosition,
    autoCompleteSuggestions,
    open,
    selectedSuggestionIndex,
    currentFilter,
  ];

  React.useEffect(() => {
    if (equals(autoCompleteSuggestions, []) || !isSearchFieldFocus) {
      setAutocompleteAnchor(null);

      return;
    }

    setAutocompleteAnchor(searchRef?.current as HTMLDivElement);
  }, [autoCompleteSuggestions]);

  const acceptAutocompleteSuggestionAtIndex = (index: number) => {
    setNewFilter();

    const acceptedSuggestion = autoCompleteSuggestions[index];

    if (equals(search[cursorPosition], ',')) {
      setSearch(search + acceptedSuggestion);
      return;
    }

    const searchBeforeCursor = search.slice(0, cursorPosition + 1);
    // the search is composed of "expressions" separated by whitespaces
    // (like "status:OK" for instance)
    const expressionBeforeCursor =
      last(searchBeforeCursor.trim().split(' ')) || '';

    // an expression is "complete" when it has a value that is not in the middle of an input
    // ("status:"" or "status:OK", for instance, but not "status:O")
    const isExpressionComplete =
      expressionBeforeCursor.endsWith(':') ||
      expressionBeforeCursor.endsWith(',') ||
      acceptedSuggestion.startsWith(',');

    const expressionAfterSeparator = isExpressionComplete
      ? ''
      : last(expressionBeforeCursor.split(/:|,/)) || '';

    const completedWord = acceptedSuggestion.slice(
      expressionAfterSeparator.length,
      acceptedSuggestion.length,
    );

    const cursorCompletionShift =
      acceptedSuggestion.length - expressionAfterSeparator.length;

    const isExpressionEmpty = expressionAfterSeparator === '';
    const searchCutPosition = isExpressionEmpty
      ? cursorPosition + 1
      : cursorPosition;

    const searchBeforeCompletedWord = search.slice(0, searchCutPosition);
    const searchAfterCompletedWord = search.slice(searchCutPosition);

    const searchWithAcceptedSuggestion = [
      searchBeforeCompletedWord.trim(),
      completedWord,
      searchAfterCompletedWord.trim() === '' ? '' : ' ',
      searchAfterCompletedWord,
    ].join('');

    setCursorPosition(cursorPosition + cursorCompletionShift);

    if (isNil(search[cursorPosition])) {
      setSearch(searchWithAcceptedSuggestion);

      return;
    }

    // when the autocompletion takes part somewhere that is not at the end of the output,
    // we need to shift the corresponding expression to the end, because that's where the cursor will end up
    const expressionToShiftToTheEnd = expressionBeforeCursor.includes(':')
      ? expressionBeforeCursor + completedWord
      : acceptedSuggestion;

    setSearch(
      [
        searchWithAcceptedSuggestion
          .replace(expressionToShiftToTheEnd, '')
          .trim(),
        ' ',
        expressionToShiftToTheEnd,
      ].join(''),
    );
  };

  const inputKey = (event: React.KeyboardEvent): void => {
    const enterKeyPressed = event.key === 'Enter';
    const tabKeyPressed = event.key === 'Tab';
    const escapeKeyPressed = event.key === 'Escape';
    const arrowDownKeyPressed = event.key === 'ArrowDown';
    const arrowUpKeyPressed = event.key === 'ArrowUp';
    const arrowLeftKeyPressed = event.key === 'ArrowLeft';
    const arrowRightKeyPressed = event.key === 'ArrowRight';

    if (arrowLeftKeyPressed || arrowRightKeyPressed) {
      updateCursorPosition();

      return;
    }

    const hasAutocompleteSuggestions = !isEmpty(autoCompleteSuggestions);
    const suggestionCount = length(autoCompleteSuggestions);

    if (arrowDownKeyPressed && hasAutocompleteSuggestions) {
      event.preventDefault();
      const newIndex = inc(selectedSuggestionIndex);

      setSelectedSuggestionIndex(newIndex >= suggestionCount ? 0 : newIndex);

      return;
    }

    if (arrowUpKeyPressed && hasAutocompleteSuggestions) {
      event.preventDefault();
      const newIndex = dec(selectedSuggestionIndex);

      setSelectedSuggestionIndex(newIndex < 0 ? suggestionCount - 1 : newIndex);

      return;
    }

    if (escapeKeyPressed) {
      closeSuggestionPopover();

      return;
    }

    if (tabKeyPressed && hasAutocompleteSuggestions) {
      event.preventDefault();
      acceptAutocompleteSuggestionAtIndex(selectedSuggestionIndex);

      return;
    }

    if (enterKeyPressed) {
      applyCurrentFilter();
      setAutocompleteAnchor(null);
      searchRef?.current?.blur();
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
    setAutocompleteAnchor(null);
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
                onClick={() => {
                  setCursorPosition(searchRef?.current?.selectionStart || 0);
                }}
                onFocus={() => setIsSearchFieldFocus(true)}
                onKeyDown={inputKey}
              />
              <Popper
                anchorEl={autocompleteAnchor}
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
                        selected={index === selectedSuggestionIndex}
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
