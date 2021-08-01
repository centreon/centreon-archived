import * as React from 'react';

import { isEmpty, propEq, pick, find, equals, last, head } from 'ramda';
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
import {
  getAutocompleteSuggestionPrefix,
  getAutocompleteSuggestions,
  getAutocompleteSuggestionSuffix,
} from './Criterias/searchQueryLanguage';

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

  const [anchorEl, setAnchorEl] = React.useState<HTMLDivElement | null>(null);
  const searchRef = React.useRef<HTMLInputElement>();
  const [autoCompleteSuggestions, setAutoCompleteSuggestions] = React.useState<
    Array<string>
  >([]);
  const [cursorPosition, setCursorPosition] = React.useState(0);

  const open = Boolean(anchorEl);

  const currentSearchCursorPosition = searchRef?.current?.selectionStart || 0;

  React.useEffect(() => {
    setAutoCompleteSuggestions(
      getAutocompleteSuggestions({
        cursorPosition,
        search,
      }),
    );
  }, [search, cursorPosition]);

  // const autoCompleteSuggestions = getAutocompleteSuggestions({
  //   cursorPosition,
  //   search,
  // });

  const memoProps = [
    customFilters,
    customFiltersLoading,
    search,
    cursorPosition,
    autoCompleteSuggestions,
  ];

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

    if (tabKeyPressed && !isEmpty(autoCompleteSuggestions)) {
      const lastSuggestion = autoCompleteSuggestions[0];

      if (search[cursorPosition] === ',') {
        setSearch(search + lastSuggestion);
      } else {
        const searchUntilCursor = search.slice(0, cursorPosition + 1);
        const lastWord = last(searchUntilCursor.split(' ')) || '';

        const lastWordBeforeStop =
          lastWord.endsWith(':') ||
          lastWord.endsWith(',') ||
          lastSuggestion.startsWith(',')
            ? ''
            : last(lastWord.slice(0, cursorPosition).split(/:|,/)) || '';

        const cursorShift = lastSuggestion.length - lastWordBeforeStop.length;

        const newSearch = [
          search.slice(
            0,
            lastWordBeforeStop === '' ? cursorPosition + 1 : cursorPosition,
          ),
          lastSuggestion.substring(
            lastWordBeforeStop.length,
            lastSuggestion.length,
          ),
          search.slice(
            lastWordBeforeStop === '' ? cursorPosition + 1 : cursorPosition,
          ),
        ].join('');

        setSearch(newSearch);
        const newCursorPosition = cursorPosition + cursorShift;
        setCursorPosition(newCursorPosition);
      }

      setNewFilter();

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

          <ClickAwayListener onClickAway={closeSuggestionPopover}>
            <div>
              <TextField
                autoFocus
                fullWidth
                inputRef={searchRef as React.RefObject<HTMLInputElement>}
                placeholder={t(labelSearch)}
                value={search}
                onChange={prepareSearch}
                onKeyDown={requestSearchOnEnterKey}
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
                  {autoCompleteSuggestions.map((suggestion) => {
                    return (
                      <MenuItem key={suggestion} onClick={() => undefined}>
                        {suggestion}
                      </MenuItem>
                    );
                  })}
                </Paper>
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
