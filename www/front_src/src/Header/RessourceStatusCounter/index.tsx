import { useState, useRef, useEffect } from 'react';

import axios from 'axios';
import * as yup from 'yup';
import { useAtomValue } from 'jotai/utils';

import { ClickAwayListener } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { MenuSkeleton } from '@centreon/ui';
import { refreshIntervalAtom } from '@centreon/ui-context';

export const useStyles = makeStyles((theme) => ({
  link: {
    textDecoration: 'none',
  },
  subMenuCounters: {
    display: 'flex',
    gap: theme.spacing(0.5),

    [theme.breakpoints.down(900)]: {
      display: 'grid',
      gridTemplateColumns: 'auto auto',
    },
  },
  subMenuCounters_marginLeft: {
    marginLeft: theme.spacing(-1),
    [theme.breakpoints.down(900)]: {
      marginLeft: 0,
    },
  },
  subMenuRight: {
    alignItem: 'flex-start',
    display: 'flex',
    flexDirection: 'column',
    gap: theme.spacing(0.8),
    justifyContent: 'space-between',
    [theme.breakpoints.down(900)]: {
      alignItems: 'center',
      flexDirection: 'row',
      gap: theme.spacing(0.1),
    },
  },
  subMenuToggle: {
    backgroundColor: theme.palette.common.black,
    boxSizing: 'border-box',
    display: 'none',
    left: theme.spacing(0),
    padding: theme.spacing(1),
    position: 'absolute',
    textAlign: 'left',
    top: '100%',
    width: '135%',
    [theme.breakpoints.down(900)]: {
      width: '150%',
    },
    zIndex: theme.zIndex.mobileStepper,
  },
  subMenuToggleActive: {
    display: 'block',
  },
  wrapMiddleIcon: {
    display: 'flex',
  },
}));

interface Props {
  children: (props) => JSX.Element;
  endpoint: string;
  loaderWidth: number;
  schema: yup.AnySchema;
}

const RessourceStatusCounter = <
  StatusCount extends {
    pending: number;
  },
>({
  endpoint,
  schema,
  children,
  loaderWidth,
}: Props): JSX.Element | null => {
  const [data, setData] = useState<StatusCount>();
  const [toggled, setToggled] = useState<boolean>();
  const [isAllowed, setIsAllowed] = useState<boolean>(true);

  const interval = useRef<number>();

  const refreshInterval = useAtomValue(refreshIntervalAtom);

  const getData = (): void => {
    axios
      .get(`./api/${endpoint}`)
      .then(({ data: retrievedData }) => {
        schema.validate(retrievedData).then(() => {
          setData(retrievedData);
        });
      })
      .catch((error) => {
        if (error.response && error.response.status === 401) {
          setIsAllowed(false);
        }
      });
  };

  useEffect(() => {
    getData();

    interval.current = window.setInterval(() => {
      getData();
    }, refreshInterval * 1000);

    return (): void => {
      clearInterval(interval.current);
    };
  }, []);

  const toggleDetailedView = (): void => {
    setToggled(!toggled);
  };

  if (!isAllowed) {
    return null;
  }

  if (!data) {
    return <MenuSkeleton width={loaderWidth} />;
  }

  const hasPending = data.pending > 0;

  return (
    <ClickAwayListener
      onClickAway={(): void => {
        if (!toggled) {
          return;
        }

        toggleDetailedView();
      }}
    >
      {children({ data, hasPending, toggleDetailedView, toggled })}
    </ClickAwayListener>
  );
};

export default RessourceStatusCounter;
