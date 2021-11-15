import React from 'react';

import * as yup from 'yup';
import axios from 'axios';

import PollerIcon from '@material-ui/icons/DeviceHub';
import { ClickAwayListener } from '@material-ui/core';

import { useUserContext } from '@centreon/centreon-frontend/packages/ui-context/src';

import MenuLoader from '../../components/MenuLoader';

interface Props {
  children: (props) => JSX.Element;
  endpoint: string;
  loaderWidth: number;
  schema: yup.AnySchema;
}

const PollerMenu = ({
  schema,
  endpoint,
  loaderWidth,
  children,
}: Props): JSX.Element => {
  const [data, setData] = React.useState<boolean>();
  const [toggled, setToggled] = React.useState<boolean>(false);
  const toggleDetailedView = (): void => {
    setToggled(!toggled);
  };

  const interval = React.useRef<number>();

  const { refreshInterval } = useUserContext();

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

  if (!data) {
    return <MenuLoader width={loaderWidth} />;
  }

  return (
    <PollerIcon>
      <ClickAwayListener
        onClickAway={(): void => {
          if (!toggled) {
            return;
          }

          toggleDetailedView();
        }}
      >
        {children({ data, toggleDetailedView, toggled })}
      </ClickAwayListener>
    </PollerIcon>
  );
};

export default PollerMenu;
