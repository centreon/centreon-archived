import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Link } from '@material-ui/core';

import {
  labelApiNotFoundNotUpToDate,
  labelApiNotFoundContactAdmin,
  labelApiNotFoundDocumentation,
} from '../translatedLabels';

const ApiNotFoundMessage = (): JSX.Element => {
  const { t } = useTranslation();

  return (
    <>
      <p style={{ margin: 0 }}>{`${t(labelApiNotFoundNotUpToDate)}.`}</p>
      <p style={{ margin: 0 }}>
        {`${t(
          labelApiNotFoundContactAdmin,
        )} : /opt/rh/httpd24/root/etc/httpd/conf.d/10-centreon.conf`}
      </p>
      <p style={{ margin: 0 }}>
        <Link
          color="inherit"
          href="https://docs.centreon.com/21.10/en/upgrade/upgrade-from-19-10.html#configure-apache-api-access"
          target="_blank"
        >
          {`( ${t(labelApiNotFoundDocumentation)} )`}
        </Link>
      </p>
    </>
  );
};

export default ApiNotFoundMessage;
