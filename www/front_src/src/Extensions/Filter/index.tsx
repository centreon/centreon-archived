import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useUpdateAtom } from 'jotai/utils';
import { useAtom } from 'jotai';

import makeStyles from '@mui/styles/makeStyles';
import CloseIcon from '@mui/icons-material/Close';

import {
  MemoizedFilter,
  SearchField,
  IconButton,
  LoadingSkeleton,
} from '@centreon/ui';

import { labelSearch, labelClearFilter } from '../translatedLabels';

import { clearFilterDerivedAtom, searchAtom } from './filterAtoms'; // a lire

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

  // const [isSearchFieldFocus, setIsSearchFieldFocused] = React.useState(false);
  // change depend of the focus in searchField
  const searchRef = React.useRef<HTMLInputElement>(); // ref to SearchField

  const [cursorPosition, setCursorPosition] = React.useState(0); // get the character near the mouse curseur of searchField text

  const [search, setSearch] = useAtom(searchAtom); // Default ''

  const clearFilter = useUpdateAtom(clearFilterDerivedAtom);

  const updateCursorPosition = (): void => {
    setCursorPosition(searchRef?.current?.selectionStart || 0);
  };

  React.useEffect(() => {
    updateCursorPosition();
  }, [searchRef?.current?.selectionStart]);

  // const blurInput = (): void => {
  //   setIsSearchFieldFocused(false);
  // };

  const prepareSearch = (event): void => {
    const { value } = event.target;
    setSearch(value);
  };

  const inputKey = (event: React.KeyboardEvent): void => {
    // const enterKeyPressed = event.key === 'Enter';
    // const tabKeyPressed = event.key === 'Tab';
    // const escapeKeyPressed = event.key === 'Escape';
    // const arrowDownKeyPressed = event.key === 'ArrowDown';
    // const arrowUpKeyPressed = event.key === 'ArrowUp';
    const arrowLeftKeyPressed = event.key === 'ArrowLeft';
    const arrowRightKeyPressed = event.key === 'ArrowRight';

    if (arrowLeftKeyPressed || arrowRightKeyPressed) {
      updateCursorPosition();
    }

    // The rest need to add AutoSuggestion feature
  };

  const memoProps = [search, cursorPosition];

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
          <SearchField
            // fullWidth // ???
            EndAdornment={renderClearFilter(clearFilter)} // return a button to clear the searchField - read more about clearFilterDerivedAtom
            inputRef={searchRef as React.RefObject<HTMLInputElement>} // ref to searchField
            placeholder={t(labelSearch)} // normal label
            value={search} // passe the value of SearchField
            // onBlur={blurInput} // make the IsSearchFieldFocused state false - clearDebounceDynamicSuggestions
            onChange={prepareSearch} // call state Search to change the search value
            onClick={(): void => {
              setCursorPosition(searchRef?.current?.selectionStart || 0); // selectionStart ????
            }}
            // onFocus={(): void => setIsSearchFieldFocused(true)}
            onKeyDown={inputKey} // controle the Search using keyboard
          />
        </div>
      }
      memoProps={memoProps}
    />
  );
};

export default Filter;
