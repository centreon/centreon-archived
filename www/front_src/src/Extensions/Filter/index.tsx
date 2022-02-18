import * as React from 'react';

import {
  isEmpty,
  equals,
  last,
  inc,
  length,
  dec,
  isNil,
  dropLast,
} from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import { useAtom } from 'jotai';

import makeStyles from '@mui/styles/makeStyles';
import CloseIcon from '@mui/icons-material/Close';
import { ClickAwayListener, MenuItem, Paper, Popper } from '@mui/material';

import {
  MemoizedFilter,
  SearchField,
  IconButton,
  LoadingSkeleton,
} from '@centreon/ui';

import { labelSearch, labelClearFilter } from '../translatedLabels';

import { getAutocompleteSuggestions } from './Criterias/searchQueryLanguage';
import {
  currentFilterCriteriasAtom,
  applyCurrentFilterDerivedAtom,
  clearFilterDerivedAtom,
  searchAtom,
} from './filterAtoms';

const useStyles = makeStyles((theme) => ({
  autocompletePopper: {
    zIndex: theme.zIndex.tooltip,
  },
  container: {
    alignItems: 'center',
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(2),
    gridTemplateColumns: '20px auto',
    width: '100%',
  },
  loader: { display: 'flex', justifyContent: 'center' },
}));

const Criterias = React.lazy(() => import('./Criterias'));

const renderClearFilter = (onClear) => (): JSX.Element => {
  const { t } = useTranslation();

  return (
    <IconButton
      ariaLabel={t(labelClearFilter)}
      size="small"
      title={t(labelClearFilter)}
      onClick={onClear}
    >
      <CloseIcon color="action" fontSize="small" />
    </IconButton>
  );
};

const Filter = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const [isSearchFieldFocus, setIsSearchFieldFocused] = React.useState(false);

  const [autocompleteAnchor, setAutocompleteAnchor] =
    React.useState<HTMLDivElement | null>(null);

  const searchRef = React.useRef<HTMLInputElement>();

  const [autoCompleteSuggestions, setAutoCompleteSuggestions] = React.useState<
    Array<string>
  >([]);

  const [cursorPosition, setCursorPosition] = React.useState(0);

  const [selectedSuggestionIndex, setSelectedSuggestionIndex] =
    React.useState(0);

  const [search, setSearch] = useAtom(searchAtom);
  const currentFilter = useAtomValue(currentFilterCriteriasAtom);
  const applyCurrentFilter = useUpdateAtom(applyCurrentFilterDerivedAtom);
  const clearFilter = useUpdateAtom(clearFilterDerivedAtom);

  const open = Boolean(autocompleteAnchor);

  React.useEffect(() => {
    setSelectedSuggestionIndex(0);

    if (isEmpty(search.charAt(dec(cursorPosition)).trim())) {
      setAutoCompleteSuggestions([]);
      setAutocompleteAnchor(null);

      return;
    }

    setAutoCompleteSuggestions([]);

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

  React.useEffect(() => {
    if (isEmpty(autoCompleteSuggestions)) {
      setAutocompleteAnchor(null);

      return;
    }

    setAutocompleteAnchor(searchRef?.current as HTMLDivElement);
  }, [autoCompleteSuggestions]);

  const acceptAutocompleteSuggestionAtIndex = (index: number): void => {
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

    const searchBeforeSuggestion = isEmpty(expressionAfterSeparator.trim())
      ? searchBeforeCompletedWord.trim()
      : dropLast(
          expressionAfterSeparator.length,
          searchBeforeCompletedWord.trim(),
        );

    const suggestion = isEmpty(expressionAfterSeparator.trim())
      ? completedWord
      : acceptedSuggestion;

    const searchWithAcceptedSuggestion = [
      searchBeforeSuggestion,
      suggestion,
      searchAfterCompletedWord.trim() === '' ? '' : ' ',
      searchAfterCompletedWord,
    ].join('');

    setCursorPosition(cursorPosition + cursorCompletionShift);
    setAutoCompleteSuggestions([]);

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
      setAutoCompleteSuggestions([]);

      return;
    }

    const isSearchFieldFocusedAndEnterKeyPressed =
      enterKeyPressed && isSearchFieldFocus;

    const canAcceptSuggestion =
      tabKeyPressed || isSearchFieldFocusedAndEnterKeyPressed;

    if (canAcceptSuggestion && hasAutocompleteSuggestions) {
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
  };

  const closeSuggestionPopover = (): void => {
    setAutocompleteAnchor(null);
  };

  const blurInput = (): void => {
    setIsSearchFieldFocused(false);
  };

  const memoProps = [
    search,
    cursorPosition,
    autoCompleteSuggestions,
    open,
    selectedSuggestionIndex,
    currentFilter,
  ];

  return (
    <MemoizedFilter
      content={
        <div className={classes.container}>
          <React.Suspense
            fallback={
              <LoadingSkeleton height={24} variant="circular" width={24} />
            }
          >
            <Criterias />
          </React.Suspense>
          <ClickAwayListener onClickAway={closeSuggestionPopover}>
            <div>
              <SearchField
                fullWidth
                EndAdornment={renderClearFilter(clearFilter)}
                inputRef={searchRef as React.RefObject<HTMLInputElement>}
                placeholder={t(labelSearch)}
                value={search}
                onBlur={blurInput}
                onChange={prepareSearch}
                onClick={(): void => {
                  setCursorPosition(searchRef?.current?.selectionStart || 0);
                }}
                onFocus={(): void => setIsSearchFieldFocused(true)}
                onKeyDown={inputKey}
              />
              <Popper
                anchorEl={autocompleteAnchor}
                className={classes.autocompletePopper}
                open={open}
                style={{
                  width: searchRef?.current?.clientWidth,
                }}
              >
                <Paper square>
                  {autoCompleteSuggestions.map((suggestion, index) => {
                    return (
                      <MenuItem
                        key={suggestion}
                        selected={index === selectedSuggestionIndex}
                        onClick={(): void => {
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
