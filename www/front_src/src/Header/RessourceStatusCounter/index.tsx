import * as React from 'react';

import axios from 'axios';
import * as yup from 'yup';
import { useAtomValue } from 'jotai/utils';

import { ClickAwayListener } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { refreshIntervalAtom } from '@centreon/ui-context';

import MenuLoader from '../../components/MenuLoader';

export const useStyles = makeStyles(() => ({
  link: {
    textDecoration: 'none',
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
}: Props): JSX.Element => {
  const [data, setData] = React.useState<StatusCount>();
  const [toggled, setToggled] = React.useState<boolean>();

  const interval = React.useRef<number>();

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
          setData(undefined);
        }
      });
  };

  React.useEffect(() => {
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

  if (!data) {
    return <MenuLoader width={loaderWidth} />;
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
