import { useState, useRef, useEffect } from 'react';

import axios from 'axios';
import * as yup from 'yup';
import { useAtomValue } from 'jotai/utils';

import { ClickAwayListener } from '@mui/material';

import { MenuSkeleton } from '@centreon/ui';
import { refreshIntervalAtom } from '@centreon/ui-context';

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
